import {
  IsDateString,
  IsIn,
  IsNumber,
  IsOptional,
  IsString,
  Max,
  MaxLength,
  Min,
} from 'class-validator';

export class NoteDto {
  @IsString()
  eleveId!: string;

  @IsString()
  matiereId!: string;

  @IsString()
  classeId!: string;

  @IsString()
  trimestreId!: string;

  @IsIn(['devoir', 'composition'])
  type!: 'devoir' | 'composition';

  @IsNumber()
  @Min(0)
  valeur!: number;

  @IsNumber()
  @Min(1)
  @Max(100)
  bareme!: number;

  @IsNumber()
  @Min(0.5)
  @Max(10)
  coefficient!: number;

  @IsDateString()
  dateEvaluation!: string;

  @IsOptional()
  @IsString()
  @MaxLength(255)
  commentaire?: string;

  @IsOptional()
  @IsString()
  enseignantId?: string;
}
