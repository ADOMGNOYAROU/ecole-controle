import { Type } from 'class-transformer';
import {
  ArrayMinSize,
  IsArray,
  IsDateString,
  IsIn,
  IsOptional,
  IsString,
  MaxLength,
  ValidateIf,
  ValidateNested,
} from 'class-validator';

const estAbsent = (dto: PresenceBulkEntryDto) => dto.statut === 'absent';

export class PresenceBulkEntryDto {
  @IsString()
  eleveId!: string;

  @IsIn(['present', 'absent', 'retard'])
  statut!: 'present' | 'absent' | 'retard';

  @ValidateIf(estAbsent)
  @IsString()
  @MaxLength(255)
  motif?: string;
}

export class PresenceBulkDto {
  @IsString()
  classeId!: string;

  @IsOptional()
  @IsString()
  trimestreId?: string;

  @IsDateString()
  date!: string;

  @IsArray()
  @ArrayMinSize(1)
  @ValidateNested({ each: true })
  @Type(() => PresenceBulkEntryDto)
  presences!: PresenceBulkEntryDto[];
}
