import { createSignal, onMount } from 'solid-js';

export const setFlash = (message: string) => {
  sessionStorage.setItem('flash', message);
};

export const FlashMessage = () => {
  const [flash] = createSignal(sessionStorage.getItem('flash'));
  const [visible, setVisible] = createSignal(false);

  const closeFlash = () => {
    setVisible(false);
  };

  onMount(() => {
    if (flash()) {
      sessionStorage.removeItem('flash');
      setVisible(true);
    }
  });

  return (
    <>
      {visible() && (
        <div role="alert" class="alert alert-success mb-4 items-start gap-3 rounded-lg shadow-sm">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 shrink-0"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <span class="grow leading-relaxed">{flash()}</span>
          <button type="button" class="btn btn-ghost btn-xs btn-circle" onClick={closeFlash} aria-label="閉じる">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
          </button>
        </div>
      )}
    </>
  );
};
