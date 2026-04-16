export const parseBooleanQuery = (value: boolean | string | null | undefined) => {
  if (value === true || value === 'true') return true;
  if (value === false || value === 'false') return false;
  return undefined;
};
