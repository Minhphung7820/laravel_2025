export function encodeQuery(obj) {
    const json = JSON.stringify(obj)
    const utf8Bytes = new TextEncoder().encode(json)
    const base64 = btoa(String.fromCharCode(...utf8Bytes))
    return base64
}

export function decodeQuery(str) {
    const binary = atob(str)
    const bytes = new Uint8Array([...binary].map(char => char.charCodeAt(0)))
    const json = new TextDecoder().decode(bytes)
    return JSON.parse(json)
}