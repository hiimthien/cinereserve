import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useBookingStore } from '../stores/bookingStore';
import { useAuthStore } from '../stores/authStore';

export function useCheckout() {
  const router = useRouter();
  const store = useBookingStore();
  const authStore = useAuthStore();

  const paymentMethod = ref<'vnpay' | 'momo' | 'card'>('vnpay');
  const userName = ref(authStore.user?.name || 'Cao Lương Thiện');
  const userEmail = ref(authStore.user?.email || 'caoluongthienk1@gmail.com');
  const userPhone = ref(authStore.user?.phone || '0388145796');

  const cardNumber = ref('4242 •••• •••• 4242');
  const cardExpiry = ref('12/28');
  const cardCvv = ref('888');
  const isLoading = ref(false);

  const isVerifyingPayment = ref(false);
  const verificationStep = ref<'checking' | 'success'>('checking');

  const snackTotal = ref(0);
  const selectedCombos = ref<any[]>([]);

  const discountAmount = ref(0);
  const appliedVoucherCode = ref('');

  const seatsTotal = computed(() => {
    if (store.selectedSeats.length === 0) return 0;
    return store.selectedSeats.reduce((sum, s) => {
      const p = s.price || (store.selectedShowtime?.base_price || 95000);
      return sum + p;
    }, 0);
  });

  const totalPrice = computed(() => {
    const subtotal = seatsTotal.value + snackTotal.value;
    return Math.max(0, subtotal - discountAmount.value);
  });

  const earnedPointsPreview = computed(() => {
    const multiplier = authStore.user?.membership_tier === 'diamond' ? 0.15 : authStore.user?.membership_tier === 'vip' ? 0.10 : 0.05;
    const pts = Math.round((totalPrice.value / 1000) * multiplier);
    return Math.max(1, pts);
  });

  const vietQrUrl = computed(() => {
    const amount = totalPrice.value || 95000;
    return `https://img.vietqr.io/image/MB-0388145796-compact2.png?amount=${amount}&addInfo=VE+CINERESERVE&accountName=CAO+LUONG+THIEN`;
  });

  const momoQrUrl = computed(() => {
    const amount = totalPrice.value || 95000;
    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=2|99|0388145796|CAO+LUONG+THIEN|thiencao.work@gmail.com|0|0|${amount}|VE+CINERESERVE|transfer_p2p`;
  });

  const onSnackTotalUpdate = (total: number) => {
    snackTotal.value = total;
  };

  const onCombosUpdate = (combos: any[]) => {
    selectedCombos.value = combos;
  };

  const onVoucherApplied = (voucher: { code: string; discount_amount: number; title: string } | null) => {
    if (voucher) {
      discountAmount.value = voucher.discount_amount;
      appliedVoucherCode.value = voucher.code;
    } else {
      discountAmount.value = 0;
      appliedVoucherCode.value = '';
    }
  };

  const startPaymentFlow = async () => {
    isVerifyingPayment.value = true;
    verificationStep.value = 'checking';

    setTimeout(async () => {
      verificationStep.value = 'success';
      
      setTimeout(async () => {
        isVerifyingPayment.value = false;
        await handlePay();
      }, 1200);
    }, 2200);
  };

  const handlePay = async () => {
    isLoading.value = true;
    try {
      await store.processCheckout({
        booking_code: '',
        payment_method: paymentMethod.value,
        amount: totalPrice.value,
        card_holder: userName.value,
        user_name: userName.value,
        user_email: userEmail.value,
        user_phone: userPhone.value,
        combos: selectedCombos.value,
        card_number: cardNumber.value,
        voucher_code: appliedVoucherCode.value || undefined,
        discount_amount: discountAmount.value || 0,
      } as any);

      authStore.fetchUser();
      router.push({ name: 'ticket-confirmation' });
    } catch (e) {
      console.error(e);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    store,
    authStore,
    paymentMethod,
    userName,
    userEmail,
    userPhone,
    cardNumber,
    cardExpiry,
    cardCvv,
    isLoading,
    isVerifyingPayment,
    verificationStep,
    snackTotal,
    selectedCombos,
    discountAmount,
    appliedVoucherCode,
    seatsTotal,
    totalPrice,
    earnedPointsPreview,
    vietQrUrl,
    momoQrUrl,
    onSnackTotalUpdate,
    onCombosUpdate,
    onVoucherApplied,
    startPaymentFlow,
    handlePay,
  };
}
