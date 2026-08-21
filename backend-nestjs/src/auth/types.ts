export type Role = 'super_admin' | 'admin' | 'enseignant' | 'parent' | 'eleve';

export interface AuthenticatedUser {
  uid: string;
  email: string | null;
  role: Role;
  ecoleId: string | null;
}
