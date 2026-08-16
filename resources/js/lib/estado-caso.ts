import type { VariantProps } from 'class-variance-authority';
import type { badgeVariants } from '@/components/ui/badge';
import type { EstadoCaso } from '@/types/casos';

type BadgeVariant = VariantProps<typeof badgeVariants>['variant'];

export const ESTADO_LABELS: Record<EstadoCaso, string> = {
    en_tramite: 'En trámite',
    archivado: 'Archivado',
    suspendido: 'Suspendido',
    finalizado: 'Finalizado',
};

export const ESTADO_VARIANTS: Record<EstadoCaso, BadgeVariant> = {
    en_tramite: 'default',
    suspendido: 'warning',
    archivado: 'secondary',
    finalizado: 'success',
};
