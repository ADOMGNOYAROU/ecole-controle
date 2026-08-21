export interface HorodatageFirestore {
  _seconds: number;
  _nanoseconds: number;
}

export function versDateInput(ts: HorodatageFirestore | string | null | undefined): string {
  if (!ts) return '';
  if (typeof ts === 'string') return ts.slice(0, 10);
  return new Date(ts._seconds * 1000).toISOString().slice(0, 10);
}

export function versDateAffichee(ts: HorodatageFirestore | string | null | undefined): string {
  if (!ts) return '—';
  const date = typeof ts === 'string' ? new Date(ts) : new Date(ts._seconds * 1000);
  return date.toLocaleDateString('fr-FR');
}
