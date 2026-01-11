#!/usr/bin/env python3
"""
Générateur professionnel de certificat auto-signé
Supporte RSA, ECDSA et Ed25519
Sortie : key.pem (clé privée), cert.pem (certificat), csr.pem (CSR)
Dépendance : cryptography (pip install cryptography)
"""

import ipaddress
from getpass import getpass
from datetime import datetime, timedelta, timezone
from cryptography import x509
from cryptography.x509.oid import NameOID
from cryptography.hazmat.primitives import serialization, hashes
from cryptography.hazmat.primitives.asymmetric import rsa, ec, ed25519, ed448
import sys

# ----- Fonctions utilitaires -----
def ask(prompt, default=""):
    v = input(f"{prompt}" + (f" [{default}]" if default else "") + ": ").strip()
    return v if v else default

def parse_list(s):
    return [item.strip() for item in s.split(",") if item.strip()]

def build_name(common_name, org, unit, city, state, country):
    attrs = []
    if common_name:
        attrs.append(x509.NameAttribute(NameOID.COMMON_NAME, common_name))
    if org:
        attrs.append(x509.NameAttribute(NameOID.ORGANIZATION_NAME, org))
    if unit:
        attrs.append(x509.NameAttribute(NameOID.ORGANIZATIONAL_UNIT_NAME, unit))
    if city:
        attrs.append(x509.NameAttribute(NameOID.LOCALITY_NAME, city))
    if state:
        attrs.append(x509.NameAttribute(NameOID.STATE_OR_PROVINCE_NAME, state))
    if country:
        attrs.append(x509.NameAttribute(NameOID.COUNTRY_NAME, country))
    return x509.Name(attrs)

# ----- Main -----
def main():
    print("=== Générateur de certificat auto-signé Professionnel ===\n")

    # --- Questions utilisateur ---
    common_name = ask("Nom commun (CN) — ex: example.com", "example.local")
    dns_input = ask("DNS supplémentaires (séparés par des virgules), vide si aucun", "")
    ip_input = ask("IPs supplémentaires (séparées par des virgules), vide si aucun", "")
    org = ask("Organisation (O)", "")
    unit = ask("Unité (OU)", "")
    city = ask("Ville / Localité", "")
    state = ask("État / Province", "")
    country = ask("Pays (2 lettres)", "")
    valid_days = int(ask("Durée de validité en jours", "365"))

    # --- Choix du type de clé ---
    print("\nType de clé:")
    print("1) RSA (classique, sécurisé)")
    print("2) ECDSA (rapide, moderne)")
    print("3) Ed25519 (très rapide, petit)")
    key_type = ask("Choisir 1/2/3", "1")

    if key_type == "1":
        key_size = int(ask("Taille de la clé RSA (2048 ou 4096)", "2048"))
        print(f"Génération de la clé RSA {key_size} bits...")
        private_key = rsa.generate_private_key(public_exponent=65537, key_size=key_size)
    elif key_type == "2":
        print("Courbes ECDSA disponibles:")
        print("1) SECP256R1 (256 bits)")
        print("2) SECP384R1 (384 bits)")
        curve_choice = ask("Choisir 1/2", "1")
        curve = ec.SECP256R1() if curve_choice == "1" else ec.SECP384R1()
        print(f"Génération de la clé ECDSA {curve.name}...")
        private_key = ec.generate_private_key(curve)
    elif key_type == "3":
        print("Génération de la clé Ed25519...")
        private_key = ed25519.Ed25519PrivateKey.generate()
    else:
        print("Choix invalide, utilisation RSA 2048")
        private_key = rsa.generate_private_key(public_exponent=65537, key_size=2048)

    # --- SAN ---
    dns_names = parse_list(dns_input)
    ip_list = parse_list(ip_input)
    alt_names = []

    # Ajouter CN si c'est un nom et non une IP
    if common_name:
        try:
            ipaddress.ip_address(common_name)
        except ValueError:
            alt_names.append(x509.DNSName(common_name))

    for d in dns_names:
        alt_names.append(x509.DNSName(d))
    for ip in ip_list:
        try:
            alt_names.append(x509.IPAddress(ipaddress.ip_address(ip)))
        except ValueError:
            print(f"Attention: '{ip}' n'est pas une IP valide et sera ignorée.", file=sys.stderr)

    san_extension = x509.SubjectAlternativeName(alt_names) if alt_names else None

    # --- Subject / Issuer ---
    subject = issuer = build_name(common_name, org, unit, city, state, country)

    # --- Certificat ---
    print("\nCréation du certificat (auto-signé)...")
    builder = x509.CertificateBuilder()\
        .subject_name(subject)\
        .issuer_name(issuer)\
        .public_key(private_key.public_key())\
        .serial_number(x509.random_serial_number())\
        .not_valid_before(datetime.now(timezone.utc) - timedelta(minutes=5))\
        .not_valid_after(datetime.now(timezone.utc) + timedelta(days=valid_days))\
        .add_extension(x509.BasicConstraints(ca=False, path_length=None), critical=True)

    if san_extension:
        builder = builder.add_extension(san_extension, critical=False)

    # Sign selon le type de clé
    if isinstance(private_key, (ed25519.Ed25519PrivateKey, ed448.Ed448PrivateKey)):
        certificate = builder.sign(private_key=private_key, algorithm=None)
    else:
        certificate = builder.sign(private_key=private_key, algorithm=hashes.SHA256())

    # --- CSR ---
    csr_builder = x509.CertificateSigningRequestBuilder().subject_name(subject)
    if san_extension:
        csr_builder = csr_builder.add_extension(san_extension, critical=False)
    if isinstance(private_key, (ed25519.Ed25519PrivateKey, ed448.Ed448PrivateKey)):
        csr = csr_builder.sign(private_key, algorithm=None).public_bytes(serialization.Encoding.PEM)
    else:
        csr = csr_builder.sign(private_key, hashes.SHA256()).public_bytes(serialization.Encoding.PEM)

    # --- Mot de passe clé privée ---
    protect = ask("\nProtéger la clé par mot de passe ? (y/N)", "N").lower() == "y"
    key_pass = None
    if protect:
        while True:
            p = getpass("Mot de passe pour la clé privée: ")
            p2 = getpass("Confirmez le mot de passe: ")
            if p != p2:
                print("Les mots de passe ne correspondent pas, recommencez.")
            elif p == "":
                print("Mot de passe vide non autorisé, recommencez.")
            else:
                key_pass = p.encode()
                break

    # --- Sérialisation de la clé privée (format correct selon type de clé) ---
    if isinstance(private_key, (ed25519.Ed25519PrivateKey, ed448.Ed448PrivateKey, ec.EllipticCurvePrivateKey)):
        key_format = serialization.PrivateFormat.PKCS8
    else:
        key_format = serialization.PrivateFormat.TraditionalOpenSSL

    key_pem = private_key.private_bytes(
        encoding=serialization.Encoding.PEM,
        format=key_format,
        encryption_algorithm=serialization.BestAvailableEncryption(key_pass) if key_pass else serialization.NoEncryption()
    )
    cert_pem = certificate.public_bytes(serialization.Encoding.PEM)

    # --- Écriture des fichiers ---
    with open("key.pem", "wb") as f:
        f.write(key_pem)
    with open("cert.pem", "wb") as f:
        f.write(cert_pem)
    with open("csr.pem", "wb") as f:
        f.write(csr)

    print("\n✅ Fichiers générés :")
    print(" - key.pem (clé privée)")
    print(" - cert.pem (certificat auto-signé)")
    print(" - csr.pem (CSR)")
    print("\nSécurisez key.pem (chmod 600 key.pem) et transférez avec prudence.")

# ----- Exécution -----
if __name__ == "__main__":
    main()
