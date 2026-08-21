import {
  IsInt,
  IsOptional,
  IsString,
  Max,
  MaxLength,
  Min,
} from 'class-validator';

export class ClasseDto {
  @IsString()
  @MaxLength(100)
  nom!: string;

  @IsOptional()
  @IsString()
  @MaxLength(100)
  niveau?: string;

  @IsString()
  anneeScolaireId!: string;

  @IsOptional()
  @IsString()
  enseignantPrincipalId?: string;

  @IsOptional()
  @IsInt()
  @Min(1)
  @Max(500)
  capacite?: number;
}
