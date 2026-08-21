import { IsOptional, IsString, MinLength } from 'class-validator';

export class ReinitialiserMotDePasseDto {
  @IsOptional()
  @IsString()
  @MinLength(6)
  nouveauMotDePasse?: string;
}
