<template>
  <div class="unite-org-node">
    <v-expansion-panels :model-value="isExpanded ? [0] : []">
      <v-expansion-panel>
        <v-expansion-panel-title :hide-actions="!hasChildren">
          <div class="d-flex align-center" @click.stop>
            <v-checkbox
              :model-value="unite.bcheck"
              @update:model-value="handleCheckChange"
              density="compact"
              hide-details
              @click.stop
            ></v-checkbox>
            <div
              class="cursor-pointer text-body-1"
            >
              {{ unite.nom }}
            </div>
          </div>
        </v-expansion-panel-title>
        <v-expansion-panel-text v-if="hasChildren">
          <!-- Appel récursif du même composant pour les enfants -->
          <unite-org-node
            v-for="enfant in unite.enfants"
            :key="enfant.id"
            :unite="enfant"
            :mode-choix="modeChoix"
            :depth="depth + 1"
            @choix="$emit('choix', $event)"
            class="pl-4"
          />
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface UniteOrgTree {
  id: number;
  nom: string;
  description: string;
  bcache: number;
  bcheck: boolean;
  enfants: UniteOrgTree[];
}

interface Props {
  unite: UniteOrgTree;
  modeChoix: string;
  depth?: number; // Nouveau prop pour suivre la profondeur
}

const props = withDefaults(defineProps<Props>(), {
  depth: 0 // Valeur par défaut est 0 (niveau racine)
});

const emit = defineEmits<{
  (e: 'choix', unite: UniteOrgTree): void
}>();

// Vérifie si l'unité a des enfants visibles (condition d'arrêt pour la récursion)
const hasChildren = computed(() => {
  return props.unite.enfants && props.unite.enfants.length > 0 && props.unite.bcache !== 1;
});

// Calculer si ce panel devrait être ouvert par défaut (niveau 0 = racine, niveau 1 = premier enfant)
const isExpanded = computed(() => {
  return props.depth === 0 && hasChildren.value;
});

// Gérer le changement de la case à cocher
const handleCheckChange = (value: boolean | null) => {
  if (value !== null) {
    // Ne pas modifier directement props.unite.bcheck car les props doivent être traitées comme readonly
    // Au lieu de cela, émettre un événement pour le parent
    emit('choix', props.unite);
  }
};
</script>

<style scoped>
.unite-org-node {
  margin-bottom: 4px;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
