import {
  IsDateString,
  IsIn,
  IsNumber,
  IsOptional,
  IsString,
  Min,
  MaxLength,
} from 'class-validator';

const TYPES = [
  'scolarite',
  'inscription',
  'transport',
  'cantine',
  'autre',
] as const;

export class PaiementDto {
  @IsString()
  eleveId!: string;

  @IsString()
  anneeScolaireId!: string;

  @IsIn(TYPES)
  type!: (typeof TYPES)[number];

  @IsNumber()
  @Min(0)
  montant!: number;

  @IsOptional()
  @IsNumber()
  @Min(0)
  montantPaye?: number;

  @IsDateString()
  dateEcheance!: string;

  @IsOptional()
  @IsDateString()
  datePaiement?: string;

  @IsOptional()
  @IsString()
  @MaxLength(255)
  commentaire?: string;
}
