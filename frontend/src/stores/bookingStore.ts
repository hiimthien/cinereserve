import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import type { Movie, Showtime, Seat, SeatType, SeatStatus, Booking, PaymentPayload } from '../types';
import api from '../services/api';
import { getEcho } from '../services/echo';

export const useBookingStore = defineStore('booking', () => {
  // State
  const movies = ref<Movie[]>([]);
  const currentMovie = ref<Movie | null>(null);
  const selectedDate = ref<string>(new Date().toISOString().split('T')[0]);
  const selectedShowtime = ref<Showtime | null>(null);
  const seats = ref<Seat[]>([]);
  const selectedSeats = ref<Seat[]>([]);
  const snackTotal = ref<number>(0);
  const currentBooking = ref<Booking | null>(null);
  const activeTicket = ref<Booking | null>(null);
  const bookingHistory = ref<Booking[]>([]);
  const isLoading = ref<boolean>(false);
  
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
  const seatsPrice = computed(() => {
    const base = selectedShowtime.value?.base_price || 95000;
    return selectedSeats.value.reduce((total, seat) => {
      let p = seat.price || base;
      if (seat.type === 'vip') p = base + 20000;
      if (seat.type === 'couple') p = (base * 2) + 30000;
      return total + p;
    }, 0);
  });

  const totalPrice = computed(() => {
    return seatsPrice.value + snackTotal.value;
  });

  const nowShowingMovies = computed(() => {
    const list = movies.value.filter(m => m.status === 'now_showing');
    if (list.length === 0 && movies.value.length > 0) {
      return movies.value.slice(0, Math.max(1, Math.ceil(movies.value.length * 0.65)));
    }
    return list;
  });

  const comingSoonMovies = computed(() => {
    const list = movies.value.filter(m => m.status === 'coming_soon');
    if (list.length === 0 && movies.value.length > 0) {
      return movies.value.slice(Math.max(1, Math.ceil(movies.value.length * 0.65)));
    }
    return list;
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
    isLoading.value = true;
    try {
      const response = await api.get('/movies');
      const data = response.data.data || response.data;
      if (Array.isArray(data) && data.length > 0) {
        movies.value = data;
      } else {
        mockCatalog();
      }
    } catch (error) {
      console.warn('API error, using fallback catalog:', error);
      mockCatalog();
    } finally {
      isLoading.value = false;
    }
  };

  const loadShowtimeById = async (showtimeId: number) => {
    try {
      isLoading.value = true;
      selectedSeats.value = [];
      snackTotal.value = 0;
      stopCountdown();

      const res = await api.get(`/showtimes/${showtimeId}`);
      const stData = res.data.data || res.data;
      selectedShowtime.value = stData;
      if (stData.movie) {
        currentMovie.value = stData.movie;
      }
      if (stData.show_date) {
        selectedDate.value = stData.show_date;
      }

      await fetchSeats(showtimeId);
      subscribeToSeatUpdates(showtimeId);
    } catch (err) {
      console.error('Failed to load showtime by ID', err);
      generateMockSeats(showtimeId);
    } finally {
      isLoading.value = false;
    }
  };

  const selectMovie = (movie: Movie) => {
    currentMovie.value = movie;
    if (movie.showtimes && movie.showtimes.length > 0) {
      selectedShowtime.value = movie.showtimes[0];
    }
  };

  const selectDate = (date: string) => {
    selectedDate.value = date;
  };

  const selectShowtime = async (showtime: Showtime) => {
    selectedShowtime.value = showtime;
    selectedSeats.value = [];
    snackTotal.value = 0;
    stopCountdown();
    await fetchSeats(showtime.id);
    subscribeToSeatUpdates(showtime.id);
  };

  const fetchSeats = async (showtimeId: number) => {
    try {
      const response = await api.get(`/showtimes/${showtimeId}/seats`);
      seats.value = response.data.data || response.data;
    } catch (error) {
      console.warn('API error, generating mock seats:', error);
      generateMockSeats(showtimeId);
    }
  };

  const toggleSeat = async (seat: Seat) => {
    // 1. Block click if seat is already booked
    if (seat.status === 'booked') {
      alert(`Ghế ${seat.row}${seat.number} đã được bán.`);
      return;
    }

    // 2. Block click if seat is currently held by someone else
    if (seat.status === 'holding' && seat.held_by !== sessionId.value) {
      alert(`Ghế ${seat.row}${seat.number} đang được người dùng khác giữ chỗ trong 10 phút.`);
      return;
    }

    const index = selectedSeats.value.findIndex(s => s.id === seat.id);

    if (index >= 0) {
      // Unselect & release seat
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
      // Check maximum 8 seats limit
      if (selectedSeats.value.length >= 8) {
        alert('Bạn chỉ có thể chọn tối đa 8 ghế mỗi lần đặt.');
        return;
      }

      // Optimistic Hold
      seat.status = 'selected';
      seat.held_by = sessionId.value;
      selectedSeats.value.push(seat);

      try {
        await api.post(`/showtimes/${selectedShowtime.value?.id}/seats/${seat.id}/hold`, {
          session_id: sessionId.value
        });

        if (!isTimerActive.value) {
          startCountdown(600); // 10 minutes
        }
      } catch (err: any) {
        // Rollback optimistic selection if seat was just locked by another person in Redis
        seat.status = 'holding';
        seat.held_by = 'other_user';
        const rollbackIndex = selectedSeats.value.findIndex(s => s.id === seat.id);
        if (rollbackIndex >= 0) {
          selectedSeats.value.splice(rollbackIndex, 1);
        }
        alert(err.response?.data?.message || `Ghế ${seat.row}${seat.number} vừa bị người dùng khác giữ chỗ.`);
      }
    }
  };


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

  const subscribeToSeatUpdates = (showtimeId: number) => {
    try {
      const echo = getEcho();
      echo.channel(`showtime.${showtimeId}`)
        .listen('.SeatStatusUpdated', (event: any) => {
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
      console.warn('Echo listener skipped:', e);
    }
  };

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
      bookingHistory.value.unshift(bookingResult);
      try {
        localStorage.setItem('cinereserve_booking_history', JSON.stringify(bookingHistory.value));
      } catch (e) {}

      stopCountdown();
      selectedSeats.value = [];
      return bookingResult;
    } catch (err) {
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
      bookingHistory.value.unshift(mockResult);
      try {
        localStorage.setItem('cinereserve_booking_history', JSON.stringify(bookingHistory.value));
      } catch (e) {}

      stopCountdown();
      selectedSeats.value = [];
      return mockResult;
    }
  };


  function mockCatalog() {
    // Fallback if offline
    const list: Movie[] = [
      {
        id: 1,
        title: 'Dune: Part Two',
        original_title: 'Dune: Part Two (2024)',
        slug: 'dune-part-two',
        duration: 166,
        release_date: '2024-03-01',
        poster_url: 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=600&auto=format&fit=crop&q=80',
        backdrop_url: 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?w=1600&auto=format&fit=crop&q=80',
        trailer_url: 'https://www.youtube.com/embed/Way9Dexny3w',
        rating: 8.6,
        genre: ['Sci-Fi', 'Adventure', 'Action', 'IMAX'],
        description: 'Paul Atreides hợp lực cùng Chani và người Fremen trên hành trình trả thù những kẻ đã hủy hoại gia đình mình.',
        director: 'Denis Villeneuve',
        cast: ['Timothée Chalamet', 'Zendaya', 'Rebecca Ferguson', 'Javier Bardem'],
        status: 'now_showing',
      },
      {
        id: 2,
        title: 'Oppenheimer',
        original_title: 'Oppenheimer (2023)',
        slug: 'oppenheimer',
        duration: 180,
        release_date: '2023-07-21',
        poster_url: 'https://images.unsplash.com/photo-1440404653325-ab127d49abc1?w=600&auto=format&fit=crop&q=80',
        backdrop_url: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&auto=format&fit=crop&q=80',
        trailer_url: 'https://www.youtube.com/embed/uYPbbksJxIg',
        rating: 8.9,
        genre: ['Biography', 'Drama', 'History', 'IMAX 70mm'],
        description: 'Câu chuyện về nhà vật lý lý thuyết J. Robert Oppenheimer và dự án bom nguyên tử Manhattan.',
        director: 'Christopher Nolan',
        cast: ['Cillian Murphy', 'Emily Blunt', 'Matt Damon', 'Robert Downey Jr.'],
        status: 'now_showing',
      },
      {
        id: 3,
        title: 'Deadpool & Wolverine',
        original_title: 'Deadpool & Wolverine (2024)',
        slug: 'deadpool-and-wolverine',
        duration: 128,
        release_date: '2024-07-26',
        poster_url: 'https://images.unsplash.com/photo-1568876694728-451bbf694b83?w=600&auto=format&fit=crop&q=80',
        backdrop_url: 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=1600&auto=format&fit=crop&q=80',
        trailer_url: 'https://www.youtube.com/embed/73_1biulkYk',
        rating: 8.0,
        genre: ['Action', 'Comedy', 'Sci-Fi', 'Marvel'],
        description: 'Deadpool và Wolverine tái hợp trong một cuộc phiêu lưu bảo vệ đa vũ trụ đầy hài hước.',
        director: 'Shawn Levy',
        cast: ['Ryan Reynolds', 'Hugh Jackman', 'Emma Corrin'],
        status: 'now_showing',
      },
      {
        id: 4,
        title: 'Spider-Man: Across the Spider-Verse',
        original_title: 'Spider-Man: Across the Spider-Verse (2023)',
        slug: 'spider-man-across-the-spider-verse',
        duration: 140,
        release_date: '2023-06-02',
        poster_url: 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=600&auto=format&fit=crop&q=80',
        backdrop_url: 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=1600&auto=format&fit=crop&q=80',
        trailer_url: 'https://www.youtube.com/embed/cqGjhVJWtEg',
        rating: 8.7,
        genre: ['Animation', 'Action', 'Adventure', '3D'],
        description: 'Miles Morales du hành qua các chiều không gian Đa vũ trụ.',
        director: 'Joaquim Dos Santos',
        cast: ['Shameik Moore', 'Hailee Steinfeld', 'Oscar Isaac'],
        status: 'now_showing',
      },
      {
        id: 5,
        title: 'Interstellar (10th Anniversary IMAX)',
        original_title: 'Interstellar (2014)',
        slug: 'interstellar',
        duration: 169,
        release_date: '2024-09-27',
        poster_url: 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=600&auto=format&fit=crop&q=80',
        backdrop_url: 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1600&auto=format&fit=crop&q=80',
        trailer_url: 'https://www.youtube.com/embed/zSWdZVtXT7E',
        rating: 8.7,
        genre: ['Sci-Fi', 'Drama', 'Adventure', 'IMAX'],
        description: 'Chuyến du hành tìm kiếm ngôi nhà mới cho nhân loại qua lỗ sâu không gian.',
        director: 'Christopher Nolan',
        cast: ['Matthew McConaughey', 'Anne Hathaway', 'Jessica Chastain'],
        status: 'coming_soon',
      }
    ];

    movies.value = list;
    currentMovie.value = list[0];
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
    snackTotal,
    currentBooking,
    activeTicket,
    bookingHistory,
    isLoading,
    remainingSeconds,
    formattedRemainingTime,
    isTimerActive,
    isTimeCritical,
    isTimeExpired,
    seatsPrice,
    totalPrice,
    nowShowingMovies,
    comingSoonMovies,
    sessionId,
    fetchMovies,
    loadShowtimeById,
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
