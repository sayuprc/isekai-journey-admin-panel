import type { RequestSongPerson, SongPersonRole } from '../../generated';

export type SelectedPerson = {
  personId: string;
  name: string;
};

export const addSelectedPerson = (selected: SelectedPerson[], person: SelectedPerson): SelectedPerson[] => {
  if (selected.some(item => item.personId === person.personId)) {
    return selected;
  }

  return [...selected, person];
};

export const moveSelectedPerson = (
  selected: SelectedPerson[],
  index: number,
  direction: -1 | 1,
): SelectedPerson[] => {
  const nextIndex = index + direction;
  if (nextIndex < 0 || nextIndex >= selected.length) {
    return selected;
  }

  const next = [...selected];
  const current = next[index];
  const target = next[nextIndex];
  if (!current || !target) {
    return selected;
  }

  next[index] = target;
  next[nextIndex] = current;
  return next;
};

export const toRequestSongPersons = (
  selected: SelectedPerson[],
  role: SongPersonRole,
): RequestSongPerson[] => selected.map((person, index) => ({
  personId: person.personId,
  role,
  orderNo: index + 1,
}));
