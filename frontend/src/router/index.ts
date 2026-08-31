import { createRouter, createWebHistory } from 'vue-router';
import MovieDetailView from '../views/MovieDetailView.vue';
import SeatSelectionView from '../views/SeatSelectionView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import TicketConfirmationView from '../views/TicketConfirmationView.vue';

const routes = [
  {
    path: '/',
    name: 'movie-detail',
    component: MovieDetailView,
  },
  {
    path: '/showtime/:showtimeId/seats',
    name: 'seat-selection',
    component: SeatSelectionView,
    props: true,
  },
  {
    path: '/checkout',
    name: 'checkout',
    component: CheckoutView,
  },
  {
    path: '/ticket/confirmation',
    name: 'ticket-confirmation',
    component: TicketConfirmationView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
