import { Type } from 'class-transformer';
import {
  IsArray,
  IsEmail,
  IsOptional,
  IsString,
  MaxLength,
  ValidateNested,
} from 'class-validator';

export class TuteurEleveDto {
  @IsString()
  id!: string;

  @IsString()
  @MaxLength(50)
  lienParente!: string;
}

export class TuteurDto {
  @IsString()
  @MaxLength(100)
  nom!: string;

  @IsString()
  @MaxLength(100)
  prenom!: string;

  @IsString()
  @MaxLength(30)
  telephone!: string;

  @IsOptional()
  @IsEmail()
  @MaxLength(150)
  email?: string;

  @IsOptional()
  @IsString()
  @MaxLength(150)
  profession?: string;

  @IsOptional()
  @IsString()
  @MaxLength(255)
  adresse?: string;

  @IsOptional()
  @IsArray()
  @ValidateNested({ each: true })
  @Type(() => TuteurEleveDto)
  eleves?: TuteurEleveDto[];
}
