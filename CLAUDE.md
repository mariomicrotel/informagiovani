# Informagiovani Enna Manager

Realizzare un plugin WordPress custom per la gestione completa dell'Informagiovani del Comune di Enna.

Il progetto deve essere implementato come plugin WordPress, non come tema e non come applicazione React standalone.

Stack:
- WordPress 6.x
- PHP 8.x
- MySQL
- JavaScript vanilla
- CSS prefissato con ig-enna-
- Custom Post Type
- Tabelle custom
- Shortcode
- WordPress REST API
- Admin pages WordPress

Il design di riferimento arriva da Claude Design tramite Handoff to Claude Code.

Claude Code deve rispettare:
- layout;
- componenti;
- flussi utente;
- interazioni;
- responsive design;
- area frontend;
- area personale;
- backend gestionale;
- area partner.

Priorità:
1. struttura plugin installabile;
2. ruoli e permessi;
3. tabelle custom;
4. CPT e tassonomie;
5. backend admin;
6. frontend pubblico;
7. area personale;
8. ticket, appuntamenti e colloqui;
9. eventi, partner, percorsi impresa;
10. report, newsletter, notifiche ed export.

Regole:
- non modificare il core WordPress;
- usare prefisso classi IG_Enna_;
- usare prefisso funzioni ig_enna_;
- usare nonce nei form;
- sanitizzare input;
- escapare output;
- usare capability WordPress;
- rispettare GDPR e accessibilità;
- implementare per fasi;
- non creare solo mockup statici.