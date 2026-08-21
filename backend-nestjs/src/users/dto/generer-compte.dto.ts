import {
  IsEmail,
  IsIn,
  IsOptional,
  IsString,
  MinLength,
} from 'class-validator';

export type TypeProfil = 'eleve' | 'enseignant' | 'tuteur';

export class GenererCompteDto {
  @IsIn(['eleve', 'enseignant', 'tuteur'])
  type!: TypeProfil;

  @IsString()
  profilId!: string;

  @IsOptional()
  @IsString()
  @MinLength(6)
  motDePasse?: string;

  @IsOptional()
  @IsEmail()
  emailManuel?: string;
}
