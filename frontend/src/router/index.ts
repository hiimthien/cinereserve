import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import MovieDetailView from '../views/MovieDetailView.vue';
import SeatSelectionView from '../views/SeatSelectionView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import TicketConfirmationView from '../views/TicketConfirmationView.vue';
import MyTicketsView from '../views/MyTicketsView.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView,
  },
  {
    path: '/movie/:slug',
    name: 'movie-detail',
    component: MovieDetailView,
    props: true,
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
  {
    path: '/my-tickets',
    name: 'my-tickets',
    component: MyTicketsView,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  }
});

export default router;
