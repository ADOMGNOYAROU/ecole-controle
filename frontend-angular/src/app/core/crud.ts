import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

export function crudApi<T extends { id: string }>(http: HttpClient, baseUrl: string) {
  return {
    lister: () => firstValueFrom(http.get<T[]>(baseUrl)),
    trouver: (id: string) => firstValueFrom(http.get<T>(`${baseUrl}/${id}`)),
    creer: (payload: unknown) => firstValueFrom(http.post<{ id: string }>(baseUrl, payload)),
    modifier: (id: string, payload: unknown) => firstValueFrom(http.put<void>(`${baseUrl}/${id}`, payload)),
    supprimer: (id: string) => firstValueFrom(http.delete<void>(`${baseUrl}/${id}`)),
  };
}

export function messageErreur(error: unknown): string {
  const message = (error as { error?: { message?: string } } | null)?.error?.message;
  return message ?? "Une erreur est survenue, réessayez.";
}
