export function encodeQuery(obj) {
  return btoa(JSON.stringify(obj))
}

export function decodeQuery(str) {
  try {
    return JSON.parse(atob(str))
  } catch (e) {
    return {}
  }
}