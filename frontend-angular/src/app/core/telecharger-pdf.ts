import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

export async function telechargerPdf(http: HttpClient, url: string, nomFichier: string): Promise<void> {
  const blob = await firstValueFrom(http.get(url, { responseType: 'blob' }));
  const lien = document.createElement('a');
  const objectUrl = URL.createObjectURL(blob);
  lien.href = objectUrl;
  lien.download = nomFichier;
  lien.click();
  URL.revokeObjectURL(objectUrl);
}
