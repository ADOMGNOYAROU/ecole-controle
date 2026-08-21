import {
  IsEmail,
  IsOptional,
  IsString,
  MaxLength,
  MinLength,
} from 'class-validator';

export class InscriptionEcoleDto {
  @IsString()
  @MaxLength(150)
  nomEcole!: string;

  @IsOptional()
  @IsString()
  @MaxLength(100)
  ville?: string;

  @IsOptional()
  @IsString()
  @MaxLength(30)
  telephone?: string;

  @IsString()
  @MaxLength(150)
  adminNom!: string;

  @IsEmail()
  @MaxLength(150)
  adminEmail!: string;

  @IsString()
  @MinLength(8)
  adminPassword!: string;
}
