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
        'seanceList',
        'formModal',
        'deleteModal',
        'modalTitle',
        'modalEyebrow',
        'modalSubmit'
    ];

    static values = {
        filmUrl: String,
        createUrl: String,
        seancesUrl: String,
        updateUrlTemplate: String,
        deleteUrlTemplate: String
    };

    connect() {
        this.searchTimer = null;
        this.editingId = null;
        this.pendingDeleteId = null;
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

        const isEditing = Boolean(this.editingId);
        const url = isEditing
            ? this.updateUrlTemplateValue.replace('__ID__', this.editingId)
            : this.createUrlValue;

        try {
            const response = await fetch(url, {
                method: isEditing ? 'PATCH' : 'POST',
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

            this.showFeedback(isEditing ? 'Seance mise a jour.' : 'Seance ajoutee.', 'success');
            this.resetForm(true);
            this.closeFormModal();
            this.refreshSeances();
        } catch (error) {
            this.showFeedback(isEditing ? 'Erreur lors de la mise a jour.' : 'Erreur lors de la creation.', 'error');
        }
    }

    resetForm(clearEdit = false) {
        this.filmInputTarget.value = '';
        this.filmIdTarget.value = '';
        this.dateInputTarget.value = '';
        this.timeInputTarget.value = '';
        this.versionInputTarget.value = 'VF';
        this.salleInputTarget.value = '';
        this.suggestionsTarget.innerHTML = '';

        if (clearEdit) {
            this.editingId = null;
            this.updateModalLabels(false);
        }
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
            <div
                class="admin-seance-card"
                data-seance-id="${seance.id}"
                data-film-id="${seance.film?.id || ''}"
                data-film-title="${seance.film?.title || ''}"
                data-film-status="${seance.film?.status || ''}"
                data-salle-id="${seance.salle?.id || ''}"
                data-date="${seance.date || ''}"
                data-time="${seance.time || ''}"
                data-version="${seance.version || ''}"
            >
                <div>
                    <p class="admin-film-eyebrow">${date} · ${time}</p>
                    <h3 class="admin-film-title">${filmTitle}</h3>
                    <p class="admin-film-meta"><span>${seance.version}</span><span>${endTime}</span></p>
                </div>
                <div class="admin-seance-meta">
                    <span class="admin-badge">${filmStatus}</span>
                    <span class="admin-badge">${salle}</span>
                    <div class="admin-seance-actions">
                        <button type="button" class="admin-seance-button" data-action="admin-programmation#editSeance">Modifier</button>
                        <button type="button" class="admin-seance-button admin-seance-button--danger" data-action="admin-programmation#deleteSeance">Supprimer</button>
                    </div>
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

    cancelEdit() {
        this.resetForm(true);
        this.closeFormModal();
    }

    editSeance(event) {
        const card = event.currentTarget.closest('.admin-seance-card');
        if (!card) {
            return;
        }

        this.editingId = card.dataset.seanceId || null;
        if (!this.editingId || !card.dataset.filmId) {
            this.showFeedback('Impossible de modifier une seance sans film valide.', 'error');
            return;
        }

        this.filmIdTarget.value = card.dataset.filmId || '';
        this.filmInputTarget.value = card.dataset.filmTitle
            ? `${card.dataset.filmTitle} (${card.dataset.filmStatus || ''})`
            : '';
        this.dateInputTarget.value = card.dataset.date || '';
        this.timeInputTarget.value = card.dataset.time || '';
        this.versionInputTarget.value = card.dataset.version || 'VF';
        this.salleInputTarget.value = card.dataset.salleId || '';
        this.updateModalLabels(true);
        this.openFormModal();
        this.showFeedback('Mode edition actif.', 'info');
    }

    deleteSeance(event) {
        const card = event.currentTarget.closest('.admin-seance-card');
        if (!card) {
            return;
        }

        const seanceId = card.dataset.seanceId;
        if (!seanceId) {
            return;
        }

        this.pendingDeleteId = seanceId;
        this.openDeleteModal();
    }

    async confirmDelete() {
        if (!this.pendingDeleteId) {
            return;
        }

        const url = this.deleteUrlTemplateValue.replace('__ID__', this.pendingDeleteId);

        try {
            const response = await fetch(url, { method: 'DELETE' });
            if (!response.ok) {
                const payload = await response.json().catch(() => ({}));
                this.showFeedback(payload.message || 'Suppression impossible.', 'error');
                return;
            }

            if (this.editingId === this.pendingDeleteId) {
                this.resetForm(true);
            }

            this.showFeedback('Seance supprimee.', 'success');
            this.closeDeleteModal();
            this.refreshSeances();
        } catch (error) {
            this.showFeedback('Erreur lors de la suppression.', 'error');
        }
    }

    openCreateModal() {
        this.resetForm(true);
        this.updateModalLabels(false);
        this.openFormModal();
    }

    openFormModal() {
        this.formModalTarget.classList.add('is-active');
        this.formModalTarget.setAttribute('aria-hidden', 'false');
    }

    closeFormModal() {
        this.formModalTarget.classList.remove('is-active');
        this.formModalTarget.setAttribute('aria-hidden', 'true');
    }

    openDeleteModal() {
        this.deleteModalTarget.classList.add('is-active');
        this.deleteModalTarget.setAttribute('aria-hidden', 'false');
    }

    closeDeleteModal() {
        this.deleteModalTarget.classList.remove('is-active');
        this.deleteModalTarget.setAttribute('aria-hidden', 'true');
        this.pendingDeleteId = null;
    }

    updateModalLabels(isEditing) {
        this.modalEyebrowTarget.textContent = isEditing ? 'Edition' : 'Ajout';
        this.modalTitleTarget.textContent = isEditing ? 'Modifier la seance' : 'Ajouter une seance';
        this.modalSubmitTarget.textContent = isEditing ? 'Mettre a jour' : 'Ajouter la seance';
    }
}
