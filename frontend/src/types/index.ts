export type SeatType = 'standard' | 'vip' | 'couple';
export type SeatStatus = 'available' | 'selected' | 'holding' | 'booked';

export interface Seat {
  id: number;
  room_id: number;
  row: string;
  number: number;
  type: SeatType;
  price: number;
  status: SeatStatus;
  held_by?: string | null;
  held_until?: string | null;
}

export interface Room {
  id: number;
  cinema_id: number;
  name: string;
  room_type: string; // '2D' | '3D' | 'IMAX Laser'
  total_seats: number;
  rows: string[];
}

export interface Cinema {
  id: number;
  name: string;
  address: string;
  city: string;
  rooms?: Room[];
}

export interface Showtime {
  id: number;
  movie_id: number;
  room_id: number;
  cinema_id: number;
  start_time: string;
  end_time: string;
  base_price: number;
  cinema?: Cinema;
  room?: Room;
}

export interface Movie {
  id: number;
  title: string;
  original_title: string;
  slug: string;
  duration: number; // in minutes
  release_date: string;
  poster_url: string;
  backdrop_url: string;
  trailer_url: string;
  rating: number; // e.g. 8.5
  genre: string[];
  description: string;
  director: string;
  cast: string[];
  status?: 'now_showing' | 'coming_soon' | 'archived';
  showtimes?: Showtime[];
}

export interface Booking {
  id: string;
  booking_code: string;
  user_id?: number;
  user_name: string;
  user_email: string;
  user_phone: string;
  showtime_id: number;
  total_amount: number;
  status: 'pending' | 'confirmed' | 'cancelled' | 'expired';
  expires_at: string;
  created_at: string;
  movie?: Movie;
  showtime?: Showtime;
  seats?: Seat[];
  qr_code?: string;
}

export interface PaymentPayload {
  booking_code: string;
  payment_method: 'vnpay' | 'momo' | 'card';
  amount: number;
  card_number?: string;
  card_holder?: string;
  expiry_date?: string;
  cvv?: string;
}
