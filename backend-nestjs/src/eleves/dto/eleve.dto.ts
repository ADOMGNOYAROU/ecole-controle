import {
  IsDateString,
  IsEmail,
  IsIn,
  IsOptional,
  IsString,
  MaxLength,
} from 'class-validator';

export class EleveDto {
  @IsString()
  @MaxLength(50)
  matricule!: string;

  @IsString()
  @MaxLength(100)
  nom!: string;

  @IsString()
  @MaxLength(100)
  prenom!: string;

  @IsIn(['M', 'F'])
  sexe!: 'M' | 'F';

  @IsDateString()
  dateNaissance!: string;

  @IsOptional()
  @IsString()
  @MaxLength(150)
  lieuNaissance?: string;

  @IsOptional()
  @IsString()
  @MaxLength(255)
  adresse?: string;

  @IsOptional()
  @IsString()
  @MaxLength(30)
  telephone?: string;

  @IsOptional()
  @IsEmail()
  @MaxLength(150)
  email?: string;

  @IsOptional()
  @IsString()
  classeId?: string;

  @IsIn(['actif', 'inactif', 'diplome', 'exclu'])
  statut!: 'actif' | 'inactif' | 'diplome' | 'exclu';

  @IsDateString()
  dateInscription!: string;

  @IsOptional()
  @IsString()
  @MaxLength(150)
  contactUrgenceNom?: string;

  @IsOptional()
  @IsString()
  @MaxLength(30)
  contactUrgenceTelephone?: string;
}
