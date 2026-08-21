import { Type } from 'class-transformer';
import {
  ArrayMinSize,
  IsArray,
  IsDateString,
  IsIn,
  IsNumber,
  IsOptional,
  IsString,
  Max,
  Min,
  ValidateNested,
} from 'class-validator';

export class NoteBulkEntryDto {
  @IsString()
  eleveId!: string;

  @IsNumber()
  @Min(0)
  valeur!: number;
}

export class NoteBulkDto {
  @IsString()
  classeId!: string;

  @IsString()
  matiereId!: string;

  @IsString()
  trimestreId!: string;

  @IsIn(['devoir', 'composition'])
  type!: 'devoir' | 'composition';

  @IsNumber()
  @Min(1)
  @Max(100)
  bareme!: number;

  @IsNumber()
  @Min(0.5)
  @Max(10)
  coefficient!: number;

  @IsDateString()
  dateEvaluation!: string;

  @IsOptional()
  @IsString()
  enseignantId?: string;

  @IsArray()
  @ArrayMinSize(1)
  @ValidateNested({ each: true })
  @Type(() => NoteBulkEntryDto)
  notes!: NoteBulkEntryDto[];
}
