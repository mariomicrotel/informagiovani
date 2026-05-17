# Informagiovani Enna Manager

[![PHP lint](https://github.com/mariomicrotel/informagiovani/actions/workflows/php-lint.yml/badge.svg)](https://github.com/mariomicrotel/informagiovani/actions/workflows/php-lint.yml)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-6.x-21759B?logo=wordpress&logoColor=white)
![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue)

Plugin **WordPress custom** in PHP 8.x per la gestione completa dell'Informagiovani del Comune di Enna: schede informative, eventi, ticket, appuntamenti, colloqui, partner, percorsi impresa, area personale utenti, newsletter, report e audit log.

> Implementato a partire dall'handoff visivo di [Claude Design](https://claude.ai/design) e seguendo i requisiti del file [`CLAUDE.md`](./CLAUDE.md): plugin installabile, **non** un tema, **non** una React app, **non** mockup statici.

---

## Quickstart (Docker)

Il progetto include uno stack Docker pronto all'uso (WordPress + MySQL + phpMyAdmin) sulle porte **8092** e **8093** (verificate libere — vedi note sotto).

```bash
git clone https://github.com/mariomicrotel/informagiovani.git
cd informagiovani/docker
cp .env.example .env
docker compose up -d
```

Quindi:
1. Apri http://localhost:8092 e completa il wizard di WP (lingua italiana).
2. **Plugin → Attiva** "Informagiovani Enna Manager".
3. Configura `permalink_structure` su `/%postname%/` (Impostazioni → Permalink).

phpMyAdmin: http://localhost:8093 · login con le credenziali da `.env`.

### Setup rapido via WP-CLI (dentro al container)

```bash
docker exec igenna_wp bash -lc "cd /var/www/html \
  && wp core install --url=http://localhost:8092 --title='Informagiovani Enna' \
       --admin_user=admin --admin_password='AdminPass1!' \
       --admin_email=admin@example.test --skip-email --allow-root \
  && wp rewrite structure '/%postname%/' --allow-root \
  && wp plugin activate ig-enna --allow-root"
```

---

## Shortcode pubblici

| Shortcode | Cosa fa |
|---|---|
| `[ig_enna_opportunita]` | Lista filtrabile di schede informative · filtri area/target/territorio/scadenza/search · paginazione |
| `[ig_enna_eventi]` | Calendario eventi futuri con filtro per area |
| `[ig_enna_partner]` | Griglia partner con badge tipo (Università, Camera Commercio, Azienda, ONG, …) |
| `[ig_enna_area_personale]` | Dashboard utente: login + registrazione per ospiti; tabs Panoramica/Salvati/Richieste/Percorsi/Profilo per loggati |
| `[ig_enna_newsletter]` | Form iscrizione newsletter con interessi multipli e double-opt-in |

> **Nota slug**: lo slug dell'archivio CPT `ig_scheda` è `opportunita`. Se crei una pagina con shortcode `[ig_enna_opportunita]`, usa uno slug diverso (es. `lista-opportunita`) per evitare collisione con l'archivio.

## REST API

Namespace: `wp-json/ig-enna/v1/` · autenticazione via cookie + `X-WP-Nonce`.

| Metodo | Endpoint | Auth | Descrizione |
|---|---|---|---|
| `POST` | `/saves/{id}` | logged | Salva una scheda nei preferiti utente |
| `DELETE` | `/saves/{id}` | logged | Rimuove dai preferiti |
| `GET` | `/saves` | logged | ID delle schede salvate dall'utente corrente |
| `GET` | `/tickets` | logged | Le mie richieste con paginazione |
| `POST` | `/tickets` | logged | Crea una nuova richiesta (subject, message, area_slug, priority) |

---

## Funzionalità per area

<details>
<summary><strong>📋 Schede informative (opportunità)</strong></summary>

- CPT `ig_scheda` con archivio `/opportunita/`
- Meta: codice, sintesi, tipo, scadenza, etichetta scadenza, contributo, durata, fonte (+ URL + classe ufficiale/partner/verificata), workflow state
- Workflow: bozza · da verificare · validata · pubblicata · da aggiornare · scaduta · archiviata
- Calcolo automatico urgenza scadenza (urgente ≤7gg · in scadenza ≤21gg · ok · scaduta)
- Admin: filtri (area, target, territorio, stato, urgenza, fonte) + badge nel design system
- Frontend: card con sidebar urgenza colorata, badge area/tipo/fonte, dettaglio con sezione "In sintesi"
</details>

<details>
<summary><strong>📅 Eventi</strong></summary>

- CPT `ig_evento` con archivio `/eventi/`
- Meta: data, ora, modalità (presenza/online/misto), luogo o URL, capienza, target, stato iscrizioni
- Tabella `wp_ig_enna_event_registrations` per le iscrizioni
- Admin: lista con quando, modalità, capienza/iscritti, stato badge
- Frontend: card con riquadro data (giorno + mese), badge stato iscrizioni
</details>

<details>
<summary><strong>🎫 Ticket / Richieste</strong></summary>

- Status: nuovo · assegnato · in lavorazione · in attesa utente · evaso · chiuso
- Priorità: bassa · media · alta
- SLA auto-calcolato dalle impostazioni (default 48h)
- Form utente in area personale + REST endpoint
- Admin: sub-tabs per stato con counter, filtri area, pagina dettaglio con cambio stato/priorità/assegnazione operatore
- Notifiche email automatiche a staff (nuovo ticket) e utente (cambio stato)
- Export CSV con preservazione filtri
</details>

<details>
<summary><strong>📞 Appuntamenti e colloqui</strong></summary>

- Tabelle `wp_ig_enna_appointments` e `wp_ig_enna_colloqui`
- Appuntamenti: data, range orario, modalità, stato (requested/confirmed/cancelled/done/no_show)
- Colloqui di orientamento: data, area, esito, prossimo passo
- Admin: form creazione manuale + lista
</details>

<details>
<summary><strong>👥 Area personale utente</strong></summary>

- Registrazione pubblica con consenso privacy obbligatorio
- Profilo: 14 campi (anagrafica, status, città, interessi, competenze, lingue, studi)
- Salvataggi schede via REST (toggle bookmark dalle card)
- Tabs: Panoramica (KPI) · Salvati · Richieste · Percorsi · Profilo
- Calcolo % completamento profilo
</details>

<details>
<summary><strong>🤝 Partner ed enti collegati</strong></summary>

- CPT `ig_partner` (privato, gestito da admin/responsabile/partner role)
- 7 tipi: Università/ITS, Ente pubblico, Camera Commercio, Azienda, ONG, Fondazione, Altro
- Meta: tipo, area, sito, email, telefono, indirizzo
- Shortcode `[ig_enna_partner]` per pubblicazione
</details>

<details>
<summary><strong>🚀 Percorsi impresa</strong></summary>

- CPT `ig_percorso` (privato)
- 5 tipi: Business Plan, Microcredito, Resto al Sud, Start-up giovanile, Orientamento
- Meta: tipo, durata, fasi (multilinea), referente
- Assegnazione utente↔percorso via user meta `ig_percorso_ids` (gestita dalla schermata "Modifica utente")
- Visualizzazione nel tab "Percorsi" dell'area personale
</details>

<details>
<summary><strong>📬 Newsletter</strong></summary>

- Tabella `wp_ig_enna_newsletter_subs` con double-opt-in (token 32 caratteri)
- Form pubblico con scelta interessi multipli
- Admin: filtri (tutti / confermati / da confermare), eliminazione, export CSV
</details>

<details>
<summary><strong>📊 Report & Audit</strong></summary>

- KPI dashboard: contenuti, ticket per stato, utenti, newsletter, top 10 schede salvate, prossimi 5 eventi
- Audit log centrale con auto-log su eventi sensibili (create/update ticket, publish CPT, newsletter, export)
- Pagina admin Audit con filtri per action e oggetto
</details>

---

## Architettura

```
plugin/ig-enna/
├── ig-enna.php                   ← bootstrap, costanti, require, hook attivazione
├── uninstall.php                 ← drop solo se flag impostazione attivo
├── includes/                     ← 19 classi data + helper
│   ├── class-ig-enna-plugin.php          ← singleton, registra tutti gli hook
│   ├── class-ig-enna-activator.php       ← install ruoli + tabelle + seed
│   ├── class-ig-enna-roles.php           ← 4 ruoli + 8 capability ig_enna_*
│   ├── class-ig-enna-db.php              ← dbDelta 7 tabelle
│   ├── class-ig-enna-cpt.php             ← 4 Custom Post Types
│   ├── class-ig-enna-taxonomies.php      ← 4 tassonomie + seed
│   ├── class-ig-enna-scheda-meta.php     ← metabox + workflow scheda
│   ├── class-ig-enna-evento-meta.php     ← metabox evento
│   ├── class-ig-enna-partner-meta.php    ← metabox partner
│   ├── class-ig-enna-percorso-meta.php   ← metabox percorso + assegnazione utenti
│   ├── class-ig-enna-tickets.php         ← CRUD ticket
│   ├── class-ig-enna-appointments.php    ← CRUD appuntamenti
│   ├── class-ig-enna-colloqui.php        ← CRUD colloqui
│   ├── class-ig-enna-user-profile.php    ← user meta extra
│   ├── class-ig-enna-user-saves.php      ← CRUD saves
│   ├── class-ig-enna-auth.php            ← register + profile + ticket form handlers
│   ├── class-ig-enna-rest.php            ← REST API namespace
│   ├── class-ig-enna-audit.php           ← audit log + auto-log hooks
│   ├── class-ig-enna-newsletter.php      ← subscribe + confirm + send mail
│   ├── class-ig-enna-notifications.php   ← email notifiche su ticket
│   ├── class-ig-enna-assets.php          ← enqueue CSS/JS on-demand
│   ├── class-ig-enna-shortcodes.php      ← registry shortcode
│   ├── class-ig-enna-admin-list.php      ← custom columns + filtri scheda/evento
│   └── helpers.php                       ← utility ig_enna_*
├── admin/                        ← 8 viste backend
│   ├── class-ig-enna-admin-menu.php
│   ├── class-ig-enna-admin-tickets.php
│   ├── class-ig-enna-admin-appointments.php
│   ├── class-ig-enna-admin-colloqui.php
│   ├── class-ig-enna-admin-newsletter.php
│   ├── class-ig-enna-admin-report.php
│   ├── class-ig-enna-admin-audit.php
│   ├── class-ig-enna-settings.php
│   └── views/                            ← dashboard.php, settings.php
├── public/                       ← 10 view template frontend
│   ├── class-ig-enna-frontend.php
│   ├── class-ig-enna-public.php
│   └── views/
│       ├── area-personale.php
│       ├── card-scheda.php
│       ├── form-newsletter.php
│       ├── list-eventi.php
│       ├── list-opportunita.php
│       ├── list-partner.php
│       ├── single-ig-scheda-content.php
│       └── single-ig-evento-content.php
└── assets/
    ├── css/
    │   ├── tokens.css        ← design tokens da Claude Design (palette + tipografia + spacing)
    │   ├── public.css        ← frontend
    │   └── admin.css         ← backend
    └── js/
        ├── public.js         ← REST client + save toggle
        └── admin.js
```

### Tabelle custom

| Tabella | Scopo |
|---|---|
| `wp_ig_enna_tickets` | Ticket/richieste utenti |
| `wp_ig_enna_appointments` | Appuntamenti sportello |
| `wp_ig_enna_colloqui` | Colloqui di orientamento |
| `wp_ig_enna_event_registrations` | Iscrizioni eventi |
| `wp_ig_enna_user_saves` | Bookmark schede |
| `wp_ig_enna_audit_log` | Audit trail operazioni |
| `wp_ig_enna_newsletter_subs` | Iscritti newsletter |

### Ruoli WordPress

| Ruolo | Cosa può fare |
|---|---|
| `ig_enna_responsabile` | Tutte le capability del plugin |
| `ig_enna_operator` | Gestisce ticket, eventi, vede report |
| `ig_enna_editor_schede` | Crea e pubblica schede informative |
| `ig_enna_partner` | Area partner ridotta |
| `administrator` (WP) | Riceve automaticamente tutte le `ig_enna_*` cap |

---

## Sicurezza & GDPR

- **Nonce** su tutti i form (register, profile, ticket, newsletter, admin actions, REST)
- **Sanitize input** dedicato per campo (sanitize_text_field, sanitize_email, esc_url_raw, sanitize_key, sanitize_textarea_field, wp_kses_post per messaggi ticket)
- **Escape output** (`esc_html`, `esc_attr`, `esc_url`)
- **Capability** custom mappate ai ruoli, `map_meta_cap` sui CPT
- **Audit log** disattivabile da impostazioni
- **Consenso privacy** obbligatorio in registrazione e newsletter
- **Uninstall**: dati conservati di default, drop selettivo solo se flag `delete_data_on_uninstall` attivo
- **REST**: cookie auth + WP nonce, no chiavi statiche

---

## Docker · note sulle porte

Lo stack usa **8092** (WP) e **8093** (phpMyAdmin) perché su questa macchina:

| Porta | Uso |
|---|---|
| 8080 | `comune_wp` (altro progetto) |
| 8081 | `wordpress-wordpress-1` |
| 8085 | `meteagtc` |
| 8086 | `mtdh_wordpress` |
| 8087 | `mtdh_phpmyadmin` |
| 8088 | `mtdh_mailhog` |
| 8090 | `ets-ok-main-app-1` (riservato anche se fermo) |

Per cambiare porte: modifica `docker/docker-compose.yml` (sezioni `ports`) prima di `docker compose up`.

---

## Sviluppo

### Verifica sintassi PHP

```bash
docker exec igenna_wp bash -lc \
  "find /var/www/html/wp-content/plugins/ig-enna -name '*.php' \
   -exec php -l {} \; | grep -v 'No syntax errors'"
```

### Re-attivazione plugin (utile dopo modifiche al DB schema)

```bash
docker exec igenna_wp bash -lc "cd /var/www/html \
  && wp plugin deactivate ig-enna --allow-root \
  && wp plugin activate ig-enna --allow-root"
```

### Stack control

```bash
cd docker
docker compose up -d         # avvia
docker compose logs -f wp    # log live
docker compose down          # ferma (volumi preservati)
docker compose down -v       # ferma + cancella DB
```

---

## Roadmap (fuori scope iniziale)

- [ ] Test PHPUnit + WP test suite
- [ ] CI GitHub Actions (PHP lint + WPCS)
- [ ] Traduzioni `.po` / `.mo`
- [ ] REST API estesa per app mobile
- [ ] Integrazione SMTP per invio newsletter di massa
- [ ] Hardening rate-limit su form pubblici
- [ ] WCAG AA pass completo sui template

---

## Licenza

GPL-2.0-or-later · vedi header del plugin.

## Crediti

- Design: [Claude Design](https://claude.ai/design) (handoff bundle nella root del repo)
- Implementazione: con [Claude Code](https://claude.com/claude-code)
