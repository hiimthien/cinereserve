import { animate, stagger } from 'animejs';

export function useAnime() {
  /**
   * Staggered ripple entrance animation for grid items (e.g. Seats, Movie Cards)
   */
  const animateStaggerGrid = (selector: string | HTMLElement[], options: { duration?: number; delay?: number } = {}) => {
    return animate(selector, {
      scale: [0.75, 1],
      opacity: [0, 1],
      translateY: [15, 0],
      delay: stagger(15, { from: 'center' }),
      duration: options.duration || 600,
      ease: 'outBack',
    });
  };

  /**
   * Number rolling / counting animation (e.g. CinePoints, Total Price)
   */
  const animateCount = (
    fromValue: number,
    toValue: number,
    onUpdate: (currentVal: number) => void,
    duration: number = 800
  ) => {
    const tracker = { val: fromValue };
    return animate(tracker, {
      val: toValue,
      round: 1,
      ease: 'outExpo',
      duration,
      onUpdate: () => {
        onUpdate(Math.round(tracker.val));
      },
    });
  };

  /**
   * Pop in scale spring animation for Modals and Toast notifications
   */
  const animatePop = (target: string | HTMLElement) => {
    return animate(target, {
      scale: [0.85, 1],
      opacity: [0, 1],
      duration: 350,
      ease: 'outElastic(1, .8)',
    });
  };

  return {
    animate,
    stagger,
    animateStaggerGrid,
    animateCount,
    animatePop,
  };
}
