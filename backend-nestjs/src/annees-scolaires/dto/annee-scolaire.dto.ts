import {
  IsBoolean,
  IsDateString,
  IsOptional,
  IsString,
  MaxLength,
} from 'class-validator';

export class AnneeScolaireDto {
  @IsString()
  @MaxLength(20)
  libelle!: string;

  @IsDateString()
  dateDebut!: string;

  @IsDateString()
  dateFin!: string;

  @IsOptional()
  @IsBoolean()
  active?: boolean;
}
