import {
  IsDateString,
  IsInt,
  IsString,
  Max,
  MaxLength,
  Min,
} from 'class-validator';

export class TrimestreDto {
  @IsString()
  anneeScolaireId!: string;

  @IsString()
  @MaxLength(50)
  nom!: string;

  @IsInt()
  @Min(1)
  @Max(4)
  ordre!: number;

  @IsDateString()
  dateDebut!: string;

  @IsDateString()
  dateFin!: string;
}
