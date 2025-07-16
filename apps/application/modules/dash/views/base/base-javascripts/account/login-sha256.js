async function sha256encrypt(plaintext, password) {
 const ptUtf8 = new TextEncoder().encode(plaintext);
 const pwUtf8 = new TextEncoder().encode(password);
 const pwHash = await window.crypto.subtle.digest('SHA-256', pwUtf8);

 const iv = window.crypto.getRandomValues(new Uint8Array(16));
 const alg = {
  'name': 'AES-CBC', 
  'iv': iv 
 };
 const key = await window.crypto.subtle.importKey('raw', pwHash, alg, false, ['encrypt']);

 const ctBuffer = await window.crypto.subtle.encrypt(alg, key, ptUtf8);
 const ctArray = new Uint8Array(ctBuffer);
 const ctBase64 = btoa(String.fromCharCode(...ctArray));

 const ivHex = Array.from(iv).map(b => ('00' + b.toString(16)).slice(-2)).join('');
 return (ivHex + ctBase64).toString();
}

async function sha25decrypt(ciphertext, password) {
 const ivHex = ciphertext.slice(0, 32);
 const ctBase64 = ciphertext.slice(32);
 const iv = new Uint8Array(ivHex.match(/.{1,2}/g).map(byte => parseInt(byte, 16)));

 const ctStr = atob(ctBase64);
 const ctArray = new Uint8Array(ctStr.split('').map(c => c.charCodeAt(0)));
 const pwUtf8 = new TextEncoder().encode(password);
 const pwHash = await window.crypto.subtle.digest('SHA-256', pwUtf8);

 const alg = {
  'name': 'AES-CBC', 
  'iv': iv
 };
 const key = await window.crypto.subtle.importKey('raw', pwHash, alg, false, ['decrypt']);
 
 const ptBuffer = await window.crypto.subtle.decrypt(alg, key, ctArray);
 const plaintext = new TextDecoder().decode(ptBuffer);
 return plaintext;
}