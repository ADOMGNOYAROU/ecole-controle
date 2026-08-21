import {
  IsArray,
  IsDateString,
  IsEmail,
  IsOptional,
  IsString,
  MaxLength,
} from 'class-validator';

export class EnseignantDto {
  @IsString()
  @MaxLength(100)
  nom!: string;

  @IsString()
  @MaxLength(100)
  prenom!: string;

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
  @MaxLength(150)
  specialite?: string;

  @IsOptional()
  @IsDateString()
  dateEmbauche?: string;

  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  matiereIds?: string[];

  @IsOptional()
  @IsArray()
  @IsString({ each: true })
  classeIds?: string[];
}
