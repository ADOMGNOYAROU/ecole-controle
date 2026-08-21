import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map, take } from 'rxjs';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  return authService.user$.pipe(
    take(1),
    map((user) => (user ? true : router.parseUrl('/connexion'))),
  );
};

export function roleGuard(rolesAutorises: string[]): CanActivateFn {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    return authService.user$.pipe(
      take(1),
      map((user) => {
        if (user?.role && rolesAutorises.includes(user.role)) {
          return true;
        }
        return router.parseUrl('/connexion');
      }),
    );
  };
}
