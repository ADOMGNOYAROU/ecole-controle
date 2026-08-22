import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../core/auth.service';

interface Interlocuteur {
  userId: string;
  nom: string;
  email: string | null;
}
interface DernierMessage {
  contenu: string;
  createdAt: { _seconds: number } | null;
}
interface Conversation {
  interlocuteur: Interlocuteur;
  dernierMessage: DernierMessage | null;
  nonLus: number;
}
interface Message {
  id: string;
  expediteurId: string;
  destinataireId: string;
  contenu: string;
  createdAt: { _seconds: number } | null;
}

@Component({
  selector: 'app-messagerie-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="flex h-[calc(100vh-2rem)] gap-4 p-8">
      <div class="w-72 shrink-0 overflow-y-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <h1 class="border-b border-slate-200 p-4 text-lg font-semibold text-slate-900">Messagerie</h1>
        @for (conversation of conversations(); track conversation.interlocuteur.userId) {
          <button
            (click)="ouvrir(conversation.interlocuteur)"
            class="flex w-full items-center justify-between border-b border-slate-100 p-4 text-left hover:bg-slate-50"
            [class.bg-brand-50]="selection()?.userId === conversation.interlocuteur.userId"
          >
            <div>
              <p class="text-sm font-medium text-slate-900">{{ conversation.interlocuteur.nom }}</p>
              <p class="mt-0.5 truncate text-xs text-slate-500" style="max-width: 12rem;">
                {{ conversation.dernierMessage?.contenu ?? 'Aucun message' }}
              </p>
            </div>
            @if (conversation.nonLus > 0) {
              <span class="rounded-full bg-brand-600 px-2 py-0.5 text-xs font-semibold text-white">{{
                conversation.nonLus
              }}</span>
            }
          </button>
        } @empty {
          <p class="p-4 text-sm text-slate-400">Aucun interlocuteur disponible.</p>
        }
      </div>

      <div class="flex flex-1 flex-col rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        @if (selection(); as interlocuteur) {
          <h2 class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">{{ interlocuteur.nom }}</h2>
          <div class="flex-1 space-y-2 overflow-y-auto p-4">
            @for (message of messages(); track message.id) {
              <div
                class="max-w-md rounded-lg px-3 py-2 text-sm"
                [class]="
                  message.expediteurId === monUid()
                    ? 'ml-auto bg-brand-600 text-white'
                    : 'bg-slate-100 text-slate-900'
                "
              >
                {{ message.contenu }}
              </div>
            } @empty {
              <p class="text-sm text-slate-400">Aucun message pour l'instant.</p>
            }
          </div>
          <form (ngSubmit)="envoyer()" class="flex gap-2 border-t border-slate-200 p-4">
            <input
              [(ngModel)]="brouillon"
              name="brouillon"
              placeholder="Écrire un message…"
              maxlength="2000"
              class="champ flex-1"
            />
            <button type="submit" [disabled]="!brouillon.trim()" class="btn-primary">Envoyer</button>
          </form>
        } @else {
          <div class="flex flex-1 items-center justify-center text-sm text-slate-400">
            Sélectionnez une conversation.
          </div>
        }
      </div>
    </div>
  `,
})
export class MessageriePage {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);

  readonly conversations = signal<Conversation[]>([]);
  readonly messages = signal<Message[]>([]);
  readonly selection = signal<Interlocuteur | null>(null);
  readonly monUid = signal<string | null>(null);

  brouillon = '';

  constructor() {
    void this.initialiser();
  }

  private async initialiser(): Promise<void> {
    this.authService.user$.subscribe((user) => this.monUid.set(user?.uid ?? null));
    await this.chargerConversations();
  }

  async chargerConversations(): Promise<void> {
    this.conversations.set(
      await firstValueFrom(this.http.get<Conversation[]>(`${environment.apiUrl}/messagerie`)),
    );
  }

  async ouvrir(interlocuteur: Interlocuteur): Promise<void> {
    this.selection.set(interlocuteur);
    this.messages.set(
      await firstValueFrom(this.http.get<Message[]>(`${environment.apiUrl}/messagerie/${interlocuteur.userId}`)),
    );
    await this.chargerConversations();
  }

  async envoyer(): Promise<void> {
    const interlocuteur = this.selection();
    if (!interlocuteur || !this.brouillon.trim()) return;
    await firstValueFrom(
      this.http.post(`${environment.apiUrl}/messagerie/${interlocuteur.userId}`, { contenu: this.brouillon }),
    );
    this.brouillon = '';
    await this.ouvrir(interlocuteur);
  }
}
