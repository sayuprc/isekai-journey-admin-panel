import { createSignal, onMount } from 'solid-js';

export const setFlash = (message: string) => {
  sessionStorage.setItem('flash', message);
};

export const FlashMessage = () => {
  const [flash] = createSignal(sessionStorage.getItem('flash'));
  const [visible, setVisible] = createSignal(false);

  onMount(() => {
    if (flash()) {
      sessionStorage.removeItem('flash');
      setVisible(true);
      setTimeout(() => setVisible(false), 4000);
    }
  });

  return (
    <>
      {visible() && (
        <div role="alert" class="alert alert-success mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>{flash()}</span>
        </div>
      )}
    </>
  );
};
