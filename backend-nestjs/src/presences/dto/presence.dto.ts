import {
  IsDateString,
  IsIn,
  IsOptional,
  IsString,
  MaxLength,
  ValidateIf,
} from 'class-validator';

const estAbsent = (dto: PresenceDto) => dto.statut === 'absent';

export class PresenceDto {
  @IsString()
  eleveId!: string;

  @IsString()
  classeId!: string;

  @IsOptional()
  @IsString()
  trimestreId?: string;

  @IsDateString()
  date!: string;

  @IsIn(['present', 'absent', 'retard'])
  statut!: 'present' | 'absent' | 'retard';

  @ValidateIf(estAbsent)
  @IsString()
  @MaxLength(255)
  motif?: string;
}
