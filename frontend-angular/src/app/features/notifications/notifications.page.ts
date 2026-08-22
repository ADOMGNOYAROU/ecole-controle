import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';

interface Notification {
  id: string;
  titre: string;
  message: string;
  type: string;
  lu: boolean;
  creeLe: { _seconds: number } | null;
}

@Component({
  selector: 'app-notifications-page',
  standalone: true,
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Notifications</h1>
        @if (items().some((n) => !n.lu)) {
          <button (click)="marquerToutesLues()" class="btn-ghost">Tout marquer comme lu</button>
        }
      </div>

      <div class="space-y-2">
        @for (notification of items(); track notification.id) {
          <div
            class="flex items-start justify-between rounded-xl bg-white p-4 shadow-sm ring-1"
            [class]="notification.lu ? 'ring-slate-200' : 'ring-brand-200 bg-brand-50/40'"
          >
            <div>
              <p class="text-sm font-semibold text-slate-900">{{ notification.titre }}</p>
              <p class="mt-1 text-sm text-slate-600">{{ notification.message }}</p>
              <p class="mt-1 text-xs text-slate-400">{{ dateAffichee(notification.creeLe) }}</p>
            </div>
            @if (!notification.lu) {
              <button (click)="marquerLue(notification)" class="lien shrink-0">Marquer comme lue</button>
            }
          </div>
        } @empty {
          <p class="text-sm text-slate-400">Aucune notification.</p>
        }
      </div>
    </div>
  `,
})
export class NotificationsPage {
  private readonly http = inject(HttpClient);
  readonly items = signal<Notification[]>([]);

  constructor() {
    void this.charger();
  }

  dateAffichee(ts: { _seconds: number } | null): string {
    if (!ts) return '';
    return new Date(ts._seconds * 1000).toLocaleString('fr-FR');
  }

  async charger(): Promise<void> {
    this.items.set(await firstValueFrom(this.http.get<Notification[]>(`${environment.apiUrl}/notifications`)));
  }

  async marquerLue(notification: Notification): Promise<void> {
    await firstValueFrom(this.http.patch(`${environment.apiUrl}/notifications/${notification.id}/lue`, {}));
    await this.charger();
  }

  async marquerToutesLues(): Promise<void> {
    await firstValueFrom(this.http.patch(`${environment.apiUrl}/notifications/lues`, {}));
    await this.charger();
  }
}
