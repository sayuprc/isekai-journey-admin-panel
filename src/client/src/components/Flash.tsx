import { createSignal } from 'solid-js';

export const setFlash = (message: string) => {
  sessionStorage.setItem('flash', message);
};

export const FlashMessage = () => {
  const [flash] = createSignal(sessionStorage.getItem('flash'));

  if (flash()) {
    sessionStorage.removeItem('flash');
  }

  // TODO デザインを考える
  return (
    <>
      {flash() && <div>{flash()}</div>}
    </>
  );
};
