import { customRef } from 'vue';

/**
 * Custom Ref with built-in Debounce for search inputs and rapid filters
 */
export function useDebouncedRef<T>(initialValue: T, delay: number = 300) {
  let timeout: any = null;
  return customRef<T>((track, trigger) => {
    let value = initialValue;
    return {
      get() {
        track();
        return value;
      },
      set(newValue: T) {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
          value = newValue;
          trigger();
        }, delay);
      },
    };
  });
}
