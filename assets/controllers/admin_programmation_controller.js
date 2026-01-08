import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'filmInput',
        'filmId',
        'suggestions',
        'dateInput',
        'timeInput',
        'versionInput',
        'salleInput',
        'feedback',
        'seanceList'
    ];

    static values = {
        filmUrl: String,
        createUrl: String,
        seancesUrl: String
    };

    connect() {
        this.searchTimer = null;
    }

    searchFilm() {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => this.performFilmSearch(), 200);
    }

    async performFilmSearch() {
        const query = this.filmInputTarget.value.trim();
        if (query === '') {
            this.suggestionsTarget.innerHTML = '';
            this.filmIdTarget.value = '';
            return;
        }

        try {
            const response = await fetch(`${this.filmUrlValue}?q=${encodeURIComponent(query)}`);
            if (!response.ok) {
                this.suggestionsTarget.innerHTML = '';
                return;
            }

            const payload = await response.json();
            const results = payload.results || [];
            if (results.length === 0) {
                this.suggestionsTarget.innerHTML = '<div class="admin-empty">Aucun film correspondant en base.</div>';
                return;
            }

            this.suggestionsTarget.innerHTML = results.map((film) => this.renderSuggestion(film)).join('');
        } catch (error) {
            this.suggestionsTarget.innerHTML = '';
        }
    }

    renderSuggestion(film) {
        return `
            <button
                type="button"
                class="admin-suggestion"
                data-action="click->admin-programmation#selectFilm"
                data-film-id="${film.id}"
                data-film-title="${film.title}"
                data-film-status="${film.statusLabel}"
                data-film-duration="${film.duration}"
            >
                <span class="admin-suggestion-title">${film.title}</span>
                <span class="admin-suggestion-meta">${film.statusLabel}</span>
            </button>
        `;
    }

    selectFilm(event) {
        const button = event.currentTarget;
        this.filmIdTarget.value = button.dataset.filmId;
        this.filmInputTarget.value = `${button.dataset.filmTitle} (${button.dataset.filmStatus})`;
        this.suggestionsTarget.innerHTML = '';
    }

    async createSeance(event) {
        event.preventDefault();

        const filmId = this.filmIdTarget.value;
        const salleId = this.salleInputTarget.value;
        const date = this.dateInputTarget.value;
        const time = this.timeInputTarget.value;
        const version = this.versionInputTarget.value;

        if (!filmId) {
            this.showFeedback('Merci de choisir un film present en base.', 'error');
            return;
        }

        if (!salleId || !date || !time) {
            this.showFeedback('Date, heure et salle sont obligatoires.', 'error');
            return;
        }

        try {
            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filmId: parseInt(filmId, 10),
                    salleId: parseInt(salleId, 10),
                    date,
                    time,
                    version
                })
            });

            const payload = await response.json();
            if (!response.ok) {
                this.showFeedback(payload.message || 'Enregistrement impossible.', 'error');
                return;
            }

            this.showFeedback('Seance ajoutee.', 'success');
            this.resetForm();
            this.refreshSeances();
        } catch (error) {
            this.showFeedback('Erreur lors de la creation.', 'error');
        }
    }

    resetForm() {
        this.filmInputTarget.value = '';
        this.filmIdTarget.value = '';
        this.dateInputTarget.value = '';
        this.timeInputTarget.value = '';
        this.versionInputTarget.value = 'VF';
        this.salleInputTarget.value = '';
        this.suggestionsTarget.innerHTML = '';
    }

    async refreshSeances() {
        try {
            const response = await fetch(this.seancesUrlValue);
            if (!response.ok) {
                return;
            }

            const payload = await response.json();
            const seances = payload.results || [];
            if (seances.length === 0) {
                this.seanceListTarget.innerHTML = '<div class="admin-empty">Aucune seance programmee.</div>';
                return;
            }

            this.seanceListTarget.innerHTML = seances.map((seance) => this.renderSeance(seance)).join('');
        } catch (error) {
            // swallow
        }
    }

    renderSeance(seance) {
        const date = seance.date ? this.formatDate(seance.date) : 'Date a definir';
        const time = seance.time || '--:--';
        const endTime = seance.endTime || '--:--';
        const filmTitle = seance.film?.title || 'Film supprime';
        const salle = seance.salle?.label || 'Salle ?';
        const filmStatus = seance.film?.status || 'Hors programmation';

        return `
            <div class="admin-seance-card">
                <div>
                    <p class="admin-film-eyebrow">${date} · ${time}</p>
                    <h3 class="admin-film-title">${filmTitle}</h3>
                    <p class="admin-film-meta"><span>${seance.version}</span><span>${endTime}</span></p>
                </div>
                <div class="admin-seance-meta">
                    <span class="admin-badge">${filmStatus}</span>
                    <span class="admin-badge">${salle}</span>
                </div>
            </div>
        `;
    }

    formatDate(dateString) {
        const [year, month, day] = dateString.split('-');
        return `${day}/${month}/${year}`;
    }

    showFeedback(message, type = 'info') {
        this.feedbackTarget.textContent = message;
        this.feedbackTarget.dataset.state = type;
    }
}
