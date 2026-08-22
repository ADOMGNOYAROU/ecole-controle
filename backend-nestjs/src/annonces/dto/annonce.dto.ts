import { IsIn, IsString, MaxLength, ValidateIf } from 'class-validator';

const CIBLES = ['tous', 'parents', 'enseignants', 'eleves', 'classe'] as const;

export class AnnonceDto {
  @IsString()
  @MaxLength(150)
  titre!: string;

  @IsString()
  @MaxLength(5000)
  contenu!: string;

  @IsIn(CIBLES)
  cible!: (typeof CIBLES)[number];

  @ValidateIf((dto: AnnonceDto) => dto.cible === 'classe')
  @IsString()
  classeId?: string;
}
