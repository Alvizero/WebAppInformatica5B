# Documentazione: FrienTrip

Per eseguire la web app, inserire tutta la cartella src dentro la cartella del server (es. xampp)

Caricare il database 'frientrip.sql' dentro al server sql, e configurare i vari parametri del database dentro il file shared/db_config.php

## 1. Introduzione

**FrienTrip** è una web app progettata per connettere viaggiatori con interessi e destinazioni simili, facilitando la ricerca di compagni di viaggio. L'applicazione permette agli utenti di registrare i propri viaggi e di trovare altri utenti che si recheranno nella stessa località nello stesso periodo. Inoltre, integra una funzionalità per le agenzie di viaggio, che possono pubblicare pacchetti turistici e interagire direttamente con gli utenti interessati. L'obiettivo principale è migliorare l'esperienza di viaggio promuovendo la compagnia e la condivisione.

## 2. Architettura dell'Applicazione

L'applicazione segue un'architettura **client-server**, con un backend basato su PHP e un frontend che utilizza HTML, CSS e JavaScript. I dati sono gestiti tramite un database MySQL.

*   **Frontend**: Interfaccia utente sviluppata con HTML, CSS e JavaScript. Utilizza librerie come Leaflet.js per la visualizzazione delle mappe e Nominatim per il geocoding. La navigazione e l'interazione sono gestite tramite richieste HTTP al backend.
*   **Backend**: Implementato in PHP, gestisce la logica, l'autenticazione, l'interazione con il database e la fornitura di dati al frontend tramite API.
*   **Database**: MySQL

## 3. Linguaggi e Tecnologie Utilizzate

*   **PHP**: Linguaggioz lato server per la logica e l'interazione con il database.
*   **MySQL**: Sistema di gestione per l'archiviazione dei dati.
*   **HTML5**: Linguaggio di markup per la struttura delle pagine web.
*   **CSS3**: Fogli di stile per la presentazione e il layout dell'interfaccia utente.
*   **JavaScript**: Linguaggio lato client per l'interattività delle pagine, la gestione delle mappe e le chiamate AJAX alle API backend.
*   **Leaflet.js**: Libreria JavaScript open-source per mappe interattive mobile-friendly.
*   **OpenStreetMap / Nominatim**: Servizi di mappatura e geocoding utilizzati con Leaflet.js.

## 4. Funzionalità Principali

### 4.1. Registrazione e Autenticazione Utente

Gli utenti possono registrarsi e accedere alla piattaforma. Il sistema supporta diversi livelli di utenza, gestiti tramite il file `auth.php` ognuno con specifiche funzionalità:

*   **Utente Standard (livello 255)**
    *   Registrazione e accesso alla piattaforma.
    *   Creazione, visualizzazione, modifica ed eliminazione dei propri piani di viaggio (`dashboard.php`).
    *   Ricerca di altri viaggiatori e pacchetti turistici sulla mappa (`map_view.php`).
    *   Interazione tramite chat con altri utenti e agenzie (`conversations.php`, `chat.php`, `package_chat.php`).
    *   Apertura e gestione di ticket di supporto (`supporto.php`, `supporto_chat.php`]).

*   **Agenzia (livello 3)**
    *   Accesso a un pannello dedicato (`agency.php`) per la gestione dei pacchetti turistici.
    *   Creazione, modifica ed eliminazione di pacchetti turistici, inclusa la gestione delle immagini (`admin_pacchetti_save.php`).
    *   Visualizzazione delle statistiche sui contatti ricevuti per i propri pacchetti.
    *   Gestione delle conversazioni con gli utenti interessati ai pacchetti.

*   **Amministratore (livelli 0, 1, 2)**
    *   Accesso completo al pannello di amministrazione (`admin.php`).
    *   Monitoraggio di statistiche generali dell'applicazione.
    *   Gestione completa di utenti (modifica ruoli, reset password, eliminazione).
    *   Gestione di viaggi e pacchetti turistici.
    *   Gestione dei ticket di supporto.
    *   **Super Admin (livello 0)**: Massimi privilegi di sistema.
    *   **Admin (livello 1)**: Privilegi amministrativi estesi.
    *   **Moderatore (livello 2)**: Privilegi di moderazione e gestione contenuti.

### 4.2. Gestione Viaggi Personali

Gli utenti standard possono aggiungere i propri viaggii specificando destinazione, date di inizio e fine. Queste informazioni sono private fino a quando l'utente non effettua una ricerca attiva. La gestione avviene tramite la pagina `dashboard.php` e gli endpoint API `viaggio_save.php` e `viaggio_delete.php`.

### 4.3. Matching di Viaggiatori

Il cuore dell'applicazione è la funzionalità di matching, implementata principalmente nell'endpoint `get_users.php` e visualizzata in `map_view.php. Gli utenti possono cercare altri viaggiatori basandosi sui seguenti criteri:

*   **Destinazione Geografica**: Utilizzo della posizione e un raggio di ricerca per trovare utenti nelle vicinanze di una località specifica.
*   **Date di Viaggio**: Il matching avviene per sovrapposizione delle date di inizio e fine viaggio.
*   **Nazionalità e Lingua**: Filtri aggiuntivi per trovare compagni di viaggio con la stessa nazionalità o che parlano la stessa lingua.

### 4.4. Gestione Pacchetti Turistici (per Agenzie)

Le agenzie di viaggio hanno un pannello dedicato (`agency.php`) dove possono:

*   Creare, modificare ed eliminare pacchetti turistici, specificando titolo, descrizione, prezzo, località (con geocoding), link esterno e immagini.
*   Visualizzare le statistiche sui contatti ricevuti per i propri pacchetti.
*   Gestire le conversazioni con gli utenti interessati ai pacchetti.

L'upload delle immagini e la persistenza dei dati dei pacchetti sono gestiti dall'endpoint `admin_pacchetti_save.php`.

### 4.5. Sistema di Messaggistica

L'applicazione include un sistema di messaggistica integrato che supporta diverse tipologie di conversazioni:

*   **Chat tra Utenti**: Conversazioni private tra due utenti che si sono trovati tramite la funzione di matching.
*   **Chat sui Pacchetti**: Conversazioni tra un utente interessato a un pacchetto e l'agenzia che lo ha pubblicato.
*   **Supporto Tecnico**: Gli utenti possono aprire ticket di supporto e chattare con il team di amministrazione.

Le conversazioni sono gestite tramite pagine come `conversations.php`, `chat.php`, `package_chat.php` e `supporto_chat.php` [9], con endpoint API dedicati per l'invio e la ricezione dei messaggi (`send_message.php`, `get_messages.php`, ecc.).

### 4.6. Pannello di Amministrazione

Gli utenti con privilegi di amministratore accedono a un pannello (`admin.php`) che consente la gestione completa della piattaforma, inclusi:

*   Monitoraggio di statistiche generali.
*   Gestione di utenti (modifica ruoli, reset password, eliminazione).
*   Gestione di viaggi e pacchetti.
*   Gestione dei ticket di supporto.

## 5. Struttura del Database

Il database  definito in `db_config.php`. schema e/r allegato come altro file.

## 6. API e Interazioni Backend

Il backend espone diversi endpoint API (situati nella directory `src/api/`) per gestire le interazioni tra frontend e database. Di seguito un elenco completo e le loro funzionalità principali:

*   **API di Matching e Ricerca**
    *   `get_users.php`: Recupera gli utenti che corrispondono ai criteri di ricerca per il matching di viaggiatori (destinazione, date, nazionalità, lingua), inclusi i pacchetti turistici correlati.

*   **API per la Gestione dei Viaggi (Utente Standard)**
    *   `viaggio_save.php`: Crea o aggiorna un viaggio personale dell'utente.
    *   `viaggio_delete.php`: Elimina un viaggio personale dell'utente.

*   **API per la Gestione dei Pacchetti (Agenzia)**
    *   `admin_pacchetti_save.php`: Crea o aggiorna un pacchetto turistico pubblicato da un'agenzia, gestendo anche l'upload delle immagini.
    *   `admin_pacchetti_delete.php`: Elimina un pacchetto turistico.

*   **API per la Messaggistica (Chat Utente-Utente)**
    *   `send_message.php`: Invia un messaggio in una conversazione privata tra utenti.
    *   `get_messages.php`: Recupera i messaggi di una conversazione privata tra utenti.
    *   `delete_conversation.php`: Elimina una conversazione privata tra utenti.

*   **API per la Messaggistica (Chat Pacchetto-Utente)**
    *   `send_package_message.php`: Invia un messaggio in una conversazione relativa a un pacchetto turistico.
    *   `get_package_messages.php`: Recupera i messaggi di una conversazione relativa a un pacchetto turistico.
    *   `delete_package_conversation.php`: Elimina una conversazione relativa a un pacchetto turistico.

*   **API per il Supporto Tecnico**
    *   `send_support_message.php`: Invia un messaggio in un ticket di supporto.
    *   `get_support_messages.php`: Recupera i messaggi di un ticket di supporto.
    *   `delete_support_ticket.php`: Elimina un ticket di supporto.

*   **API per l'Amministrazione (Pannello Admin)**
    *   `admin_reset_password.php`: Resetta la password di un utente dal pannello admin.
    *   `admin_ticket_close.php`: Chiude un ticket di supporto dal pannello admin.
    *   `admin_user_delete.php`: Elimina un utente dal pannello admin.
    *   `admin_user_edit.php`: Modifica i dati di un utente dal pannello admin.
    *   `admin_user_role.php`: Modifica il livello di ruolo di un utente dal pannello admin.
    *   `admin_viaggio_delete.php`: Elimina un viaggio dal pannello admin.


LINK ONLINE: https://frietripe.kesug.com/pages/index/index.php

PROBLEMI: alcuni funzione come la chat tra utenti / di supporto non funziona, dato che infinityFree blocca le richieste AJAX e le chiamate ripetute. Inoltre alcune funzioni del database non sono disponibili con questo tipo di host.

la web app localmente funziona senza problemi.