import axios from 'axios'
import type { AxiosResponse, AxiosError } from 'axios'
export interface UniteOrganisationnelle {
    iduniteorg: number;
    iduoparente: number | null;
    nomuniteorg: string;
    descriptionuniteorg: string;
    bcache: number;
    codeordre: string;
}
export interface UniteOrganisationnelleData {
    id: number
    idparent: number | null
    parentnom: string | null
    parentcodeordre: string | null
    nom: string
    description: string
    desctree: string
    idtype: number
    type: string
    idparentservice: number | null
    idparentdirection: number | null
    abreviation: string | null
    codeordre: string
    couleur: string | null
    bactif: number
    bcache: number
}

export interface ApiResponseUOL {
    success?: boolean;
    message?: string;
    data?: UniteOrganisationnelle[];
}
export interface ApiResponseUOD {
    success?: boolean;
    message?: string;
    data?: UniteOrganisationnelleData[];
}
// Interface générique pour les réponses API
export interface ApiResponse<T> {
    success: boolean
    message: string
    data?: T[]
}

export async function getUnitesOrgListe(server: string = '', page: string, jsonCriteres: string = '{}'): Promise<ApiResponseUOL> {
    console.log(jsonCriteres)
    const urluol: string = `${server}${page}`
    const params = new URLSearchParams([['jsoncriteres', jsonCriteres]])
    try {
        const response: AxiosResponse<UniteOrganisationnelle[]> = await axios.get(urluol, { params })
        const respData: ApiResponseUOL = {
            "success": true,
            "message": `ok`,
            "data": response.data
        }
        console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function getUniteOrgData(server: string = '', page: string, jsonCriteres: string = '{}'): Promise<ApiResponseUOD> {
    console.log(jsonCriteres)
    const urluod: string = `${server}${page}`
    const params = new URLSearchParams([['jsoncriteres', jsonCriteres]])
    try {
        const response: AxiosResponse<UniteOrganisationnelleData[]> = await axios.get(urluod, { params })
        const respData: ApiResponseUOD = {
            "success": true,
            "message": `ok`,
            "data": response.data
        }
        console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

export async function sauveUniteOrgData(server: string = '', page: string, jsonData: string = '{}'): Promise<ApiResponseUOD> {
    const urluos: string = `${server}${page}`
    try {
        const response: AxiosResponse<UniteOrganisationnelleData[]> = await axios.post(urluos, jsonData, {
            headers: {
                'Content-Type': 'application/json'
            }
        })
        const respData: ApiResponseUOD = {
            "success": true,
            "message": `ok`,
            "data": response.data
        }
        console.log(respData)
        return respData
    } catch (error) {
        return traiteAxiosError(error as AxiosError)
    }
}

function traiteAxiosError<T>(error: AxiosError): ApiResponse<T> {
    let msgErr: string = ''
    if (error.response) {
        msgErr = `${error.response.data}<br>${error.response.status}<br>${error.response.headers}`
    } else if (error.request.responseText) {
        msgErr = error.request.responseText
    } else {
        msgErr = error.message
    }
    const respData: ApiResponse<T> = {
        "success": false,
        "message": `ERREUR. ${msgErr}`,
    }
    return (respData)
}