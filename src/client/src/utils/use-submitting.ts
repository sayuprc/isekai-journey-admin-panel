import { createSignal } from 'solid-js';

/**
 * Hook to manage form submission state and prevent duplicate submissions.
 * Returns a signal for the submitting state and a wrapper function.
 */
export const createSubmitting = () => {
  const [isSubmitting, setIsSubmitting] = createSignal(false);

  /**
   * Wraps an async handler to prevent duplicate submissions.
   * @param handler The async function to execute
   * @returns A wrapped function that manages submission state
   */
  const withSubmitting = <T extends unknown[], R>(
    handler: (...args: T) => Promise<R>,
  ) => {
    return async (...args: T): Promise<R | undefined> => {
      if (isSubmitting()) {
        return;
      }

      setIsSubmitting(true);
      try {
        return await handler(...args);
      } finally {
        setIsSubmitting(false);
      }
    };
  };

  return { isSubmitting, withSubmitting };
};
