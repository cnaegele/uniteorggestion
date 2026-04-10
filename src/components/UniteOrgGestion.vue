<template>
    <CallerInfo :ssServer="ssServer" @callerinfo="receptionCallerInfo"></CallerInfo>
    <CallerIsInGroup :ssServer="ssServer" nomgroupe="GoelandManager"
        @calleringroup="receptionCallerInGroupGoelandManager"></CallerIsInGroup>

    <v-app>
        <v-main>
            <v-app-bar color="primary" prominent density="compact" app>
                <v-toolbar-title>Gestion des unités organisationnelles&nbsp;<small>(version {{ version
                }})</small></v-toolbar-title>
                <v-spacer></v-spacer>
                <div style="position: absolute; right: 16px;">
                    Utilisateur: {{ callerInformation?.prenom }} {{ callerInformation?.nom }} ({{
                        callerInformation?.login }}) - {{ callerInformation?.unite }}
                </div>
            </v-app-bar>
            <div v-if="messageErreur != ''" id="divErreur">{{ messageErreur }}</div>

            <div v-if="bGoelandManager">
                <v-container>
                    <v-row justify="center">
                        <v-col cols="auto">
                            <v-btn color="primary" class="mr-4" :disabled="isBtnDisabled"
                                @click="onEdition">Édition</v-btn>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn color="secondary" :disabled="isBtnDisabled" @click="onCreation">Création</v-btn>
                        </v-col>
                    </v-row>
                </v-container>

                <v-card class="pa-6 ml-6" max-width="1200" v-if="cardSaisie">
                    <v-card-title class="text-h5 mb-4">Unité organisationnelle</v-card-title>

                    <v-card-text>
                        <!-- Nom -->
                        <v-text-field v-model="form.nom" label="Nom" variant="outlined" density="comfortable"
                            class="mb-3" />

                        <!-- Description -->
                        <v-text-field v-model="form.description" label="Description" variant="outlined"
                            density="comfortable" class="mb-3" />

                        <!-- Abréviation -->
                        <v-text-field v-model="form.abreviation" label="Abréviation" variant="outlined"
                            density="comfortable" class="mb-3" style="max-width: 200px;" />

                        <!-- Hiérarchie (lecture seule) -->
                        <v-text-field :model-value="form.desctree" label="Hiérarchie" variant="outlined"
                            density="comfortable" readonly bg-color="grey-lighten-4" class="mb-3" />

                        <!-- Type (select) -->
                        <v-select v-model="form.idtype" :items="typesUO" item-title="text" item-value="value"
                            label="Type" variant="outlined" density="comfortable" class="mb-3"  style="max-width: 300px;" />

                        <!-- Unité parente (lecture seule + bouton choix) -->
                        <div class="d-flex align-center ga-3 mb-8">
                            <v-text-field :model-value="form.nomparent" label="Unité parente" variant="outlined"
                                density="comfortable" readonly bg-color="grey-lighten-4" hide-details />
                            <v-btn color="primary" variant="tonal" @click="onChoixParent">
                                Choix
                            </v-btn>
                        </div>

                        <!-- Code ordre -->
                        <div class="d-flex align-center ga-3 mb-3">
                            <v-text-field v-model="form.codeordre" label="Code ordre" variant="outlined"
                                density="comfortable" style="max-width: 200px;" />
                            <v-text-field v-model="form.codeordreparent" label="Code ordre parent" variant="outlined"
                                density="comfortable" style="max-width: 200px;" readonly bg-color="grey-lighten-4" />
                        </div>

                        <!-- Couleur -->
                        <div class="d-flex align-center ga-3 mb-3">
                            <v-text-field v-model="form.couleur" label="Couleur" variant="outlined"
                                density="comfortable" hide-details>
                                <template #prepend-inner>
                                    <div
                                        :style="{ backgroundColor: form.couleur, width: '24px', height: '24px', borderRadius: '4px', border: '1px solid #ccc' }" />
                                </template>
                            </v-text-field>

                            <v-menu :close-on-content-click="false">
                                <template #activator="{ props }">
                                    <v-btn v-bind="props" color="primary" variant="tonal" icon="mdi-palette" />
                                </template>
                                <v-color-picker v-model="form.couleur" mode="hexa" />
                            </v-menu>
                        </div>

                        <!-- Actif -->
                        <v-switch v-model="form.bactif" label="Actif" color="primary" :true-value="true"
                            :false-value="false" hide-details class="mb-3" />

                        <!-- Visible -->
                        <v-switch v-model="form.bvisible" label="Visible" color="primary" :true-value="true"
                            :false-value="false" hide-details class="mb-3" />
                    </v-card-text>

                    <v-card-actions class="px-6 pb-4">
                        <v-spacer />
                        <v-btn variant="outlined" color="grey" @click="onQuitter">
                            {{ isModified ? 'Quitter sans sauver' : 'Quitter' }}
                        </v-btn>
                        <v-btn v-if="isModified" variant="flat" color="primary" @click="onSauver">
                            Sauver
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </div>
        </v-main>
    </v-app>

    <v-dialog v-model="dialogChoixUO" max-width="1280" persistent>
        <v-card>
            <v-card-text>
                <Suspense>
                    <UniteOrgChoix :ssServer="ssServer" :modeChoix="modeChoixUO" :contexteChoix="contexteChoixUO"
                        @choixUniteOrg="receptionUniteOrg">
                    </UniteOrgChoix>
                </Suspense>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn color="primary" variant="elevated" text="Fermer" @click="closeChoixUO()"></v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script lang="ts" setup>
import type { ApiResponseUI, UserInfo } from './CallerInfo.vue'
import type { ApiResponseIG } from './CallerIsInGroup.vue'
import type { UniteOrganisationnelleData, ApiResponseUOD } from '@/axioscalls.ts'
import { getUniteOrgData, sauveUniteOrgData } from '@/axioscalls.ts'
import CallerInfo from '@/components/CallerInfo.vue'
import CallerIsInGroup from '@/components/CallerIsInGroup.vue'
import UniteOrgChoix from '@/components/UniteOrgChoix.vue'
import { nextTick, reactive, ref, watch } from 'vue'
import packageJson from '../../package.json'
//Data caller et droits caller
const callerInformation = ref<UserInfo | null | undefined>(null)
const bGoelandManager = ref<boolean>(false)
const version = ref<string>(packageJson.version)
const messageErreur = ref<string | undefined>('')
const messageInfo = ref<string>('')

const ssServer = ref<string>('')
if (import.meta.env.DEV) {
    ssServer.value = 'https://mygolux.lausanne.ch'
}
const ssPageData = ref<string>('/goeland/uniteorg/axios/uniteorg_data.php')
const ssPageSauve = ref<string>('/goeland/uniteorg/axios/uniteorg_sauve.php')

const modeChoixUO = ref<string>('unique')
const contexteChoixUO = ref<string>('')
const dialogChoixUO = ref<boolean>(false)
const cardSaisie = ref<boolean>(false)
const isBtnDisabled = ref(false)
const isModified = ref(false)
const loading = ref(false)

const onEdition = () => {
    isBtnDisabled.value = true
    dialogChoixUO.value = true
    contexteChoixUO.value = 'edition'
}

const onCreation = () => {
    isBtnDisabled.value = true
    dialogChoixUO.value = true
    contexteChoixUO.value = 'creation'
}

// État réactif du formulaire
const form = reactive({
    id: 0,
    idparent: 0,
    nom: '',
    description: '',
    abreviation: '',
    desctree: '',
    idtype: 0,
    nomparent: '',
    codeordreparent: '',
    codeordre: '',
    couleur: '',
    bactif: true,
    bvisible: true
})

// Options du select Type
const typesUO = [
    { value: 0, text: '- choisir le type d\'unité' },
    { value: 2, text: 'Direction' },
    { value: 3, text: 'Service' },
    { value: 7, text: 'Division' },
    { value: 6, text: 'Unité' },
]

watch(form, () => {
    if (!loading.value) {
        isModified.value = true
    }
}, { deep: true })

const receptionUniteOrg = async (jsonData: string) => {
    const oData = JSON.parse(jsonData)
    const jsonCriteres: { uniteorgid: number } = { "uniteorgid": oData.id }
    const response: ApiResponseUOD = await getUniteOrgData(ssServer.value, ssPageData.value, JSON.stringify(jsonCriteres))
    const uniteOrgData: UniteOrganisationnelleData[] = response.success && response.data ? response.data : []
    console.log(uniteOrgData[0])

    if (contexteChoixUO.value === 'edition') {
        if (uniteOrgData.length > 0) {
            dialogChoixUO.value = false
            cardSaisie.value = true
            const item = uniteOrgData[0]
            loading.value = true
            form.id = item.id
            form.idparent = item.idparent ?? 0
            form.nom = item.nom
            form.description = item.description
            if (item.abreviation !== 'NULL') {
                form.abreviation = item.abreviation ?? ''
            }
            form.desctree = item.desctree
            form.idtype = item.idtype
            form.nomparent = item.parentnom ?? ''
            form.codeordreparent = item.parentcodeordre ?? ''
            form.codeordre = item.codeordre ?? ''
            if (item.couleur !== 'NULL') {
                form.couleur = item.couleur ?? ''
            }
            form.bactif = item.bactif === 0 === false
            form.bvisible = item.bcache === 1 === false
            await nextTick()
            loading.value = false
        }
    }
    else if (contexteChoixUO.value === 'creation') {
        if (uniteOrgData.length > 0) {
            dialogChoixUO.value = false
            cardSaisie.value = true
            const item = uniteOrgData[0]
            loading.value = true
            form.desctree = `${item.desctree} / ${form.nom}`
            form.idparent = item.id
            form.nomparent = item.nom ?? ''
            form.codeordreparent = item.codeordre ?? ''
            form.codeordre = `${item.codeordre ?? ''}.`
            await nextTick()
            loading.value = false
        }
    }
    else if (contexteChoixUO.value === 'choixparent') {
        if (uniteOrgData.length > 0) {
            dialogChoixUO.value = false
            const item = uniteOrgData[0]
            form.desctree = `${item.desctree} / ${form.nom}`
            form.idparent = item.id
            form.nomparent = item.nom ?? ''
            form.codeordreparent = item.codeordre ?? ''
            //form.codeordreparent = item.codeordre ?? ''
        }
    }

}

const onChoixParent = () => {
    dialogChoixUO.value = true
    contexteChoixUO.value = 'choixparent'
}

const closeChoixUO = (): void => {
    dialogChoixUO.value = false
    isBtnDisabled.value = false
}

const onQuitter = () => {
    cardSaisie.value = false
    isBtnDisabled.value = false
}

const onSauver = async () => {
    isModified.value = false
    const jsonData: string = JSON.stringify(form)
    const response: ApiResponseUOD = await sauveUniteOrgData(ssServer.value, ssPageSauve.value, JSON.stringify(form))
    const uniteOrgData: UniteOrganisationnelleData[] = response.success && response.data ? response.data : []
    console.log(uniteOrgData)
}

const receptionCallerInfo = (jsonData: string) => {
    const retCallerInformation = ref<ApiResponseUI>(JSON.parse(jsonData))
    if (retCallerInformation.value.success) {
        callerInformation.value = retCallerInformation.value.data
    }
}

const receptionCallerInGroupGoelandManager = (jsonData: string) => {
    const retCallerInGroup = ref<ApiResponseIG>(JSON.parse(jsonData))
    if (retCallerInGroup.value.success && retCallerInGroup.value.data !== undefined) {
        bGoelandManager.value = retCallerInGroup.value.data.isingroup
    }
    if (!bGoelandManager.value) {
        messageErreur.value = "Page réservée aux managers goéland"
    }
}
</script>