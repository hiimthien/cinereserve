import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Movie, Showtime, Seat, SeatType, SeatStatus, Booking, PaymentPayload } from '../types';
import api from '../services/api';
import { getEcho } from '../services/echo';

export const useBookingStore = defineStore('booking', () => {
  // State
  const movies = ref<Movie[]>([]);
  const currentMovie = ref<Movie | null>(null);
  const selectedDate = ref<string>('2026-08-31');
  const selectedShowtime = ref<Showtime | null>(null);
  const seats = ref<Seat[]>([]);
  const selectedSeats = ref<Seat[]>([]);
  const currentBooking = ref<Booking | null>(null);
  const activeTicket = ref<Booking | null>(null);
  
  // Timer (10 minutes = 600s)
  const remainingSeconds = ref<number>(600);
  const isTimerActive = ref<boolean>(false);
  let timerInterval: any = null;

  // Session ID for tracking user's seat holding
  const sessionId = ref<string>(
    localStorage.getItem('cinereserve_session') || 'sess_' + Math.random().toString(36).substring(2, 12)
  );
  localStorage.setItem('cinereserve_session', sessionId.value);

  // Computed
  const totalPrice = computed(() => {
    return selectedSeats.value.reduce((total, seat) => total + (seat.price || 12), 0);
  });

  const formattedRemainingTime = computed(() => {
    const mins = Math.floor(remainingSeconds.value / 60);
    const secs = remainingSeconds.value % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  });

  const isTimeCritical = computed(() => remainingSeconds.value <= 60 && remainingSeconds.value > 0);
  const isTimeExpired = computed(() => remainingSeconds.value <= 0 && isTimerActive.value);

  // Actions
  const fetchMovies = async () => {
    try {
      const response = await api.get('/movies');
      movies.value = response.data.data || response.data;
      if (movies.value.length > 0 && !currentMovie.value) {
        currentMovie.value = movies.value[0];
      }
    } catch (error) {
      console.warn('API error, using fallback mock data for Movie:', error);
      mockDuneMovie();
    }
  };

  const selectMovie = (movie: Movie) => {
    currentMovie.value = movie;
  };

  const selectDate = (date: string) => {
    selectedDate.value = date;
  };

  const selectShowtime = async (showtime: Showtime) => {
    selectedShowtime.value = showtime;
    selectedSeats.value = [];
    stopCountdown();
    await fetchSeats(showtime.id);
    subscribeToSeatUpdates(showtime.id);
  };

  const fetchSeats = async (showtimeId: number) => {
    try {
      const response = await api.get(`/showtimes/${showtimeId}/seats`);
      seats.value = response.data.data || response.data;
    } catch (error) {
      console.warn('API error, using fallback mock seats matrix:', error);
      generateMockSeats(showtimeId);
    }
  };

  // Toggle seat selection & send hold request
  const toggleSeat = async (seat: Seat) => {
    if (seat.status === 'booked') return;
    if (seat.status === 'holding' && seat.held_by !== sessionId.value) return;

    const index = selectedSeats.value.findIndex(s => s.id === seat.id);

    if (index >= 0) {
      // Unselect
      selectedSeats.value.splice(index, 1);
      seat.status = 'available';
      seat.held_by = null;
      try {
        await api.post(`/showtimes/${selectedShowtime.value?.id}/seats/${seat.id}/release`, {
          session_id: sessionId.value
        });
      } catch (err) {
        console.warn('Seat release err:', err);
      }
      if (selectedSeats.value.length === 0) {
        stopCountdown();
      }
    } else {
      // Select
      if (selectedSeats.value.length >= 8) {
        alert('You can select a maximum of 8 seats per booking.');
        return;
      }
      seat.status = 'selected';
      seat.held_by = sessionId.value;
      selectedSeats.value.push(seat);

      try {
        await api.post(`/showtimes/${selectedShowtime.value?.id}/seats/${seat.id}/hold`, {
          session_id: sessionId.value
        });
      } catch (err) {
        console.warn('Seat hold API err:', err);
      }

      if (!isTimerActive.value) {
        startCountdown(600); // 10 minutes
      }
    }
  };

  // Timer controls
  const startCountdown = (seconds: number = 600) => {
    stopCountdown();
    remainingSeconds.value = seconds;
    isTimerActive.value = true;
    timerInterval = setInterval(() => {
      if (remainingSeconds.value > 0) {
        remainingSeconds.value--;
      } else {
        stopCountdown();
        handleSessionExpiry();
      }
    }, 1000);
  };

  const stopCountdown = () => {
    if (timerInterval) {
      clearInterval(timerInterval);
      timerInterval = null;
    }
    isTimerActive.value = false;
  };

  const handleSessionExpiry = () => {
    selectedSeats.value.forEach(s => {
      s.status = 'available';
      s.held_by = null;
    });
    selectedSeats.value = [];
  };

  // Real-time WebSocket subscription
  const subscribeToSeatUpdates = (showtimeId: number) => {
    try {
      const echo = getEcho();
      echo.channel(`showtime.${showtimeId}`)
        .listen('.SeatStatusUpdated', (event: any) => {
          console.log('Real-time seat update received:', event);
          const seatToUpdate = seats.value.find(s => s.id === event.seat_id);
          if (seatToUpdate) {
            if (event.held_by === sessionId.value && event.status === 'holding') {
              seatToUpdate.status = 'selected';
            } else {
              seatToUpdate.status = event.status;
            }
            seatToUpdate.held_by = event.held_by;
          }
        });
    } catch (e) {
      console.warn('Echo websocket listener skipped or offline:', e);
    }
  };

  // Process checkout & payment
  const processCheckout = async (paymentData: PaymentPayload) => {
    try {
      const response = await api.post('/bookings/checkout', {
        showtime_id: selectedShowtime.value?.id,
        seat_ids: selectedSeats.value.map(s => s.id),
        session_id: sessionId.value,
        total_amount: totalPrice.value,
        ...paymentData
      });

      const bookingResult = response.data.data || response.data;
      activeTicket.value = bookingResult;
      stopCountdown();
      selectedSeats.value = [];
      return bookingResult;
    } catch (err) {
      console.warn('Checkout API call fallback, creating mock ticket result');
      const mockResult: Booking = {
        id: 'BK-' + Math.floor(100000 + Math.random() * 900000),
        booking_code: 'CR-' + Math.floor(100000 + Math.random() * 900000),
        user_name: paymentData.card_holder || 'Cao Luong Thien',
        user_email: 'thiencao.work@gmail.com',
        user_phone: '+84 388 145 796',
        showtime_id: selectedShowtime.value?.id || 1,
        total_amount: totalPrice.value,
        status: 'confirmed',
        expires_at: new Date(Date.now() + 86400000).toISOString(),
        created_at: new Date().toISOString(),
        movie: currentMovie.value!,
        showtime: selectedShowtime.value!,
        seats: [...selectedSeats.value],
        qr_code: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=CINERESERVE-' + Math.floor(100000 + Math.random() * 900000)
      };
      activeTicket.value = mockResult;
      stopCountdown();
      selectedSeats.value = [];
      return mockResult;
    }
  };

  // Helper mock functions
  function mockDuneMovie() {
    const dune: Movie = {
      id: 1,
      title: 'Dune: Part Two',
      original_title: 'Dune: Part Two (2024)',
      slug: 'dune-part-two',
      duration: 166,
      release_date: '2024-03-01',
      poster_url: 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
      backdrop_url: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80',
      trailer_url: 'https://www.youtube.com/watch?v=Way9Dexny3w',
      rating: 8.6,
      genre: ['Sci-Fi', 'Adventure', 'Action', 'IMAX'],
      description: 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family. Facing a choice between the love of his life and the fate of the universe.',
      director: 'Denis Villeneuve',
      cast: ['Timothée Chalamet', 'Zendaya', 'Rebecca Ferguson', 'Javier Bardem'],
      showtimes: [
        {
          id: 1,
          movie_id: 1,
          room_id: 1,
          cinema_id: 1,
          start_time: '18:30',
          end_time: '21:16',
          base_price: 12,
          cinema: { id: 1, name: 'CineReserve IMAX - Laser', address: 'Landmark 81, B1 Floor', city: 'Ho Chi Minh City' },
          room: { id: 1, cinema_id: 1, name: 'Hall 1 (IMAX)', room_type: 'IMAX Laser', total_seats: 120, rows: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J'] }
        },
        {
          id: 2,
          movie_id: 1,
          room_id: 2,
          cinema_id: 1,
          start_time: '20:45',
          end_time: '23:31',
          base_price: 14,
          cinema: { id: 1, name: 'CineReserve IMAX - Laser', address: 'Landmark 81, B1 Floor', city: 'Ho Chi Minh City' },
          room: { id: 2, cinema_id: 1, name: 'Hall 2 (VIP Luxe)', room_type: 'VIP Gold Class', total_seats: 80, rows: ['A', 'B', 'C', 'D', 'E'] }
        },
        {
          id: 3,
          movie_id: 1,
          room_id: 3,
          cinema_id: 1,
          start_time: '22:30',
          end_time: '01:16',
          base_price: 10,
          cinema: { id: 1, name: 'CineReserve IMAX - Laser', address: 'Landmark 81, B1 Floor', city: 'Ho Chi Minh City' },
          room: { id: 3, cinema_id: 1, name: 'Hall 3 (Dolby Atmos)', room_type: '2D Atmos', total_seats: 140, rows: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'] }
        }
      ]
    };
    movies.value = [dune];
    currentMovie.value = dune;
    selectedShowtime.value = dune.showtimes![0];
    generateMockSeats(1);
  }

  function generateMockSeats(_showtimeId: number) {
    const rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J'];
    const generated: Seat[] = [];
    let idCounter = 1;

    rows.forEach((row) => {
      const isCoupleRow = row === 'J';
      const cols = isCoupleRow ? 6 : 14;

      for (let col = 1; col <= cols; col++) {
        let type: SeatType = 'standard';
        let price = 12;

        if (isCoupleRow) {
          type = 'couple';
          price = 28;
        } else if (['E', 'F', 'G'].includes(row) && col >= 4 && col <= 11) {
          type = 'vip';
          price = 18;
        }

        // Mock statuses to match the design screenshot
        let status: SeatStatus = 'available';
        if (row === 'B' && (col === 4 || col === 5)) {
          status = 'holding';
        } else if (['C', 'D'].includes(row) && (col === 1 || col === 2 || col === 13 || col === 14)) {
          status = 'booked';
        }

        generated.push({
          id: idCounter++,
          room_id: 1,
          row,
          number: col,
          type,
          price,
          status,
          held_by: status === 'holding' ? 'other_user' : null
        });
      }
    });

    seats.value = generated;
  }

  return {
    movies,
    currentMovie,
    selectedDate,
    selectedShowtime,
    seats,
    selectedSeats,
    currentBooking,
    activeTicket,
    remainingSeconds,
    formattedRemainingTime,
    isTimerActive,
    isTimeCritical,
    isTimeExpired,
    totalPrice,
    sessionId,
    fetchMovies,
    selectMovie,
    selectDate,
    selectShowtime,
    fetchSeats,
    toggleSeat,
    startCountdown,
    stopCountdown,
    processCheckout
  };
});
