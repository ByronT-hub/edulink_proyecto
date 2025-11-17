import hashlib

def hash_pan(pan: str) -> str:
    """
    Recibe el número de tarjeta (PAN)
    y devuelve un hash SHA-256.
    """
    return hashlib.sha256(pan.encode()).hexdigest()
