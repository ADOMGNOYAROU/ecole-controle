import { IsNumber, IsString, Max, MaxLength, Min } from 'class-validator';

export class MatiereDto {
  @IsString()
  @MaxLength(100)
  nom!: string;

  @IsString()
  @MaxLength(20)
  code!: string;

  @IsNumber()
  @Min(0.5)
  @Max(10)
  coefficientDefaut!: number;
}
