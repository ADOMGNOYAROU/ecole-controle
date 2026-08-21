import {
  IsInt,
  IsOptional,
  IsString,
  Matches,
  Max,
  MaxLength,
  Min,
} from 'class-validator';

const FORMAT_HEURE = /^([01]\d|2[0-3]):[0-5]\d$/;

export class CreneauHoraireDto {
  @IsString()
  classeId!: string;

  @IsString()
  matiereId!: string;

  @IsOptional()
  @IsString()
  enseignantId?: string;

  @IsInt()
  @Min(1)
  @Max(6)
  jourSemaine!: number;

  @Matches(FORMAT_HEURE)
  heureDebut!: string;

  @Matches(FORMAT_HEURE)
  heureFin!: string;

  @IsOptional()
  @IsString()
  @MaxLength(50)
  salle?: string;
}
