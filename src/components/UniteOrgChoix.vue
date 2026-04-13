<template>
  <v-card>
    <v-card-title class="d-flex align-center justify-space-between">
      <span v-if="modeChoix === 'multiple'">Sélection des unités</span>
      <span v-else>{{ txtContexte }}</span>
      <!-- Bouton de confirmation pour le mode multiple -->
      <v-btn v-if="modeChoix === 'multiple'" color="primary" @click="choixTermine">
        <span>Choix terminé</span>
      </v-btn>
    </v-card-title>
    <v-card-text>
      <!-- Composant racine qui démarre la récursion -->
      <UniteOrgNode v-for="unite in unitesOrgTree" :key="unite.id" :unite="unite" :mode-choix="modeChoix"
        @choix="choix" />
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { getUnitesOrgListe } from '@/axioscalls.ts'
import type { UniteOrganisationnelle, ApiResponseUOL } from '@/axioscalls.ts'
import UniteOrgNode from './UniteOrgNode.vue';


interface Props {
  modeChoix?: string
  contexteChoix?: string
  uniteHorsVdL?: boolean  // Optionnel et de type booléen
  ssServer?: string
  ssPage?: string
}

type UnitesOrg = UniteOrganisationnelle[]

interface UniteOrgTree {
  id: number;
  nom: string;
  description: string;
  bcache: number;
  bcheck: boolean;
  enfants: UniteOrgTree[];
}

interface UniteOrgChoix {
  id: number;
  nom: string;
  description: string;
}

const props = withDefaults(defineProps<Props>(), {
  modeChoix: 'unique',
  contexteChoix: '',
  uniteHorsVdL: false,
  ssServer: '',
  ssPage: '/goeland/uniteorg/axios/uniteorg_liste.php'
})

const modeChoix = ref<string>(props.modeChoix)
console.log(modeChoix.value)
watch(() => props.modeChoix, (newValue) => {
  modeChoix.value = newValue
})
const contexteChoix = ref<string>(props.contexteChoix)
console.log(contexteChoix.value)
let txtContexte = ref<string>('')
if (contexteChoix.value === 'edition') {
  txtContexte.value = `Sélection de l'unité à éditer`
} else if (contexteChoix.value === 'creation') {
  txtContexte.value = `Sélection de l'unité parente de la nouvelle unité`
} else if (contexteChoix.value === 'choixparent') {
  txtContexte.value = `Sélection de la nouvelle unité parente de l'unité`
} else {
  txtContexte.value = `Sélection d'une unité, contexte non prévu`
} watch(() => props.contexteChoix, (newValue) => {
  contexteChoix.value = newValue
  let txtContexte = ref<string>('')
  if (contexteChoix.value === 'edition') {
    txtContexte.value = `Sélection de l'unité à éditer`
  } else if (contexteChoix.value === 'creation') {
    txtContexte.value = `Sélection de l'unité parente de la nouvelle unité`
  } else {
    txtContexte.value = `Sélection d'une unité, contexte non prévu`
  }
})

const buniteHorsVdL = ref<boolean>(props.uniteHorsVdL)
watch(() => props.uniteHorsVdL, (newValue) => {
  buniteHorsVdL.value = newValue
})

const ssServer = ref<string>(props.ssServer)
const ssPage = ref<string>(props.ssPage)
const jsonCriteres: { unitehorsvdl: boolean } = { "unitehorsvdl": buniteHorsVdL.value }

const response: ApiResponseUOL = await getUnitesOrgListe(ssServer.value, ssPage.value, JSON.stringify(jsonCriteres))
const unitesOrgListe: UniteOrganisationnelle[] = response.success && response.data ? response.data : []

const unitesOrgTree = ref<UniteOrgTree[]>(transforUOListe2UOTree(unitesOrgListe))
console.log(unitesOrgTree.value)

const emit = defineEmits<{
  (e: 'choixUniteOrg', choix: string): void
}>()

const unitesOrgListeChoisi = ref<UniteOrgChoix[]>([])

function transforUOListe2UOTree(unitesOrgListe: UniteOrganisationnelle[]): UniteOrgTree[] {
  // Fonction interne pour créer un nœud d'arbre à partir d'une unité organisationnelle
  const createTreeNode = (uniteOrg: UniteOrganisationnelle): UniteOrgTree => ({
    id: uniteOrg.iduniteorg,
    nom: uniteOrg.nomuniteorg,
    description: uniteOrg.descriptionuniteorg,
    bcache: uniteOrg.bcache,
    bcheck: false,
    enfants: []
  })

  // Fonction récursive pour construire l'arbre
  const buildTreeRecursive = (parentId: number | null, depth: number = 0): UniteOrgTree[] => {
    // Vérifier la profondeur de récursion
    const maxdepth: number = 50
    if (depth >= maxdepth) {
      console.warn(`Limite de récursion atteinte (${maxdepth} niveaux). Possible référence cyclique détectée.`)
      return [];
    }
    // Filtrer les unités organisationnelles qui ont le parentId donné
    const childrenUnites = unitesOrgListe.filter(
      uniteOrg => uniteOrg.iduoparente === parentId
    )

    // Transformer chaque unité enfant en noeud d'arbre
    return childrenUnites.map(uniteOrg => {
      const treeNode = createTreeNode(uniteOrg)

      // Récursivement ajouter les enfants
      treeNode.enfants = buildTreeRecursive(uniteOrg.iduniteorg, depth + 1)

      return treeNode
    })
  }

  // Construire l'arbre à partir des unités racines (sans parent)
  return buildTreeRecursive(null)
}

const choix = (uniteOrg: UniteOrgTree) => {
  if (modeChoix.value === 'unique') {
    const uoChoisie: UniteOrgChoix = {
      id: uniteOrg.id,
      nom: uniteOrg.nom,
      description: uniteOrg.description,
    }
    emit('choixUniteOrg', JSON.stringify(uoChoisie))
  } else { // choix multiple
    choixMultiple(uniteOrg)
  }
}

// Fonction de choix multiple
const choixMultiple = (uniteOrg: UniteOrgTree) => {
  // Inverser l'état de la checkbox
  uniteOrg.bcheck = !uniteOrg.bcheck

  if (uniteOrg.bcheck) {
    // Ajouter uniquement si pas déjà présent
    if (!unitesOrgListeChoisi.value.some(objet => objet.id === uniteOrg.id)) {
      const uoChoisie: UniteOrgChoix = {
        id: uniteOrg.id,
        nom: uniteOrg.nom,
        description: uniteOrg.description,
      }
      unitesOrgListeChoisi.value.push(uoChoisie)
    }
  } else {
    // Supprimer l'unité si désélectionnée
    unitesOrgListeChoisi.value = unitesOrgListeChoisi.value.filter(
      objet => objet.id !== uniteOrg.id
    )
  }

  // Forcer la réactivité pour s'assurer que les changements sont reflétés dans l'UI
  unitesOrgTree.value = [...unitesOrgTree.value]
}

// Fonction de confirmation du choix
const choixTermine = () => {
  emit('choixUniteOrg', JSON.stringify(unitesOrgListeChoisi.value))
}
</script>