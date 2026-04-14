<template>
  <v-app>
    <v-main>
      <UniteOrgGestion :iduo="iduo" />
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
import UniteOrgGestion from '@/components/UniteOrgGestion.vue'
import { ref } from 'vue'

const urlParams = new URLSearchParams(window.location.search)
const iduo = ref<number | null>(null)
const prmsiduo = urlParams.get('iduo')
if (prmsiduo !== null && prmsiduo !== '') {
    iduo.value = stringToPositiveInteger(prmsiduo)
}

function stringToPositiveInteger(str: string): number | null {
    // Vérifie que c'est une string non vide
    if (!str || str.trim() === '') {
        return null;
    }

    // Convertit en number
    const num = Number(str);

    // Vérifie que c'est un nombre valide, un entier et positif
    if (isNaN(num) || !Number.isInteger(num) || num < 0) {
        return null;
    }

    return num;
}

</script>
