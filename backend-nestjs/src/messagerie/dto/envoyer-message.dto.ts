import { IsString, MaxLength } from 'class-validator';

export class EnvoyerMessageDto {
  @IsString()
  @MaxLength(2000)
  contenu!: string;
}
