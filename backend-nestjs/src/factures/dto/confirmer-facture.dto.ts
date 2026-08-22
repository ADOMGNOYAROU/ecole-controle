import { IsIn, IsOptional, IsString, MaxLength } from 'class-validator';

const METHODES_PAIEMENT = ['flooz', 'tmoney', 'virement', 'especes', 'autre'];

export class ConfirmerFactureDto {
  @IsIn(METHODES_PAIEMENT)
  methodePaiement!: string;

  @IsOptional()
  @IsString()
  @MaxLength(100)
  referenceTransaction?: string;
}
