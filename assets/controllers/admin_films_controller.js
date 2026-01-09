import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchInput', 'results', 'catalogBody'];
    static values = {
        searchUrl: String,
        importUrl: String,
        statusUrlTemplate: String
    };

    connect() {
        this.searchTimer = null;
    }

    preventSubmit(event) {
        event.preventDefault();
    }

    queueSearch() {
        clearTimeout(this.searchTimer);
        this.searchTimer = setTimeout(() => this.search(), 240);
    }

    async search() {
        const query = this.searchInputTarget.value.trim();
        if (query === '') {
            this.resultsTarget.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`${this.searchUrlValue}?q=${encodeURIComponent(query)}`);
            if (!response.ok) {
                this.resultsTarget.innerHTML = this.renderMessage('Recherche indisponible pour le moment.');
                return;
            }

            const payload = await response.json();
            const results = payload.results || [];

            if (results.length === 0) {
                this.resultsTarget.innerHTML = this.renderMessage('Aucun resultat pour cette recherche.');
                return;
            }

            this.resultsTarget.innerHTML = results.map((movie) => this.renderResult(movie)).join('');
        } catch (error) {
            this.resultsTarget.innerHTML = this.renderMessage("Impossible d'effectuer la recherche.");
        }
    }

    renderResult(movie) {
        const poster = movie.poster ? `<div class="admin-result-poster" style="background-image: url('${movie.poster}')"></div>` : '<div class="admin-result-poster is-placeholder"></div>';
        const release = movie.release_date ? `<span>${movie.release_date}</span>` : '<span>Date inconnue</span>';

        return `
            <button type="button" class="admin-result-item" data-action="click->admin-films#importMovie" data-tmdb-id="${movie.id}">
                ${poster}
                <div>
                    <p class="admin-result-title">${movie.title}</p>
                    <p class="admin-result-meta">${release}</p>
                    <p class="admin-result-hint">Importer dans la base locale</p>
                </div>
            </button>
        `;
    }

    renderMessage(message) {
        return `<div class="admin-empty">${message}</div>`;
    }

    async importMovie(event) {
        const tmdbId = event.currentTarget.dataset.tmdbId;
        if (!tmdbId) {
            return;
        }

        event.currentTarget.disabled = true;
        try {
            const response = await fetch(this.importUrlValue, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tmdbId: parseInt(tmdbId, 10) })
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                this.resultsTarget.innerHTML = this.renderMessage(error.message || 'Import impossible.');
                return;
            }

            const film = await response.json();
            this.appendFilmCard(film);
            this.resultsTarget.innerHTML = this.renderMessage('Film importe. Statut: Hors programmation.');
            this.searchInputTarget.value = '';
        } catch (error) {
            this.resultsTarget.innerHTML = this.renderMessage('Import en echec.');
        }
    }

    appendFilmCard(film) {
        const grid = this.catalogBodyTarget.querySelector('.admin-film-grid');
        const cardHtml = `
            <article class="admin-film-card" data-film-id="${film.id}">
                <div class="admin-film-poster" style="background-image: url('${film.poster ?? ''}')"></div>
                <div class="admin-film-infos">
                    <div class="admin-film-header">
                        <div>
                            <p class="admin-film-eyebrow">ID ${film.id}${film.tmdbId ? ' · Source ' + film.tmdbId : ''}</p>
                            <h3 class="admin-film-title">${film.title}</h3>
                            <p class="admin-film-meta">
                                <span>${film.duration ?? '?'} min</span>
                            </p>
                        </div>
                        <div class="admin-film-status is-archived">Hors programmation</div>
                    </div>
                    <div class="admin-film-actions">
                        <label class="admin-toggle">
                            <input
                                type="checkbox"
                                class="admin-toggle-input"
                                data-action="change->admin-films#toggleStatus"
                                data-film-id="${film.id}"
                            >
                            <span class="admin-toggle-slider"></span>
                            <span class="admin-toggle-label">A l'affiche</span>
                        </label>
                        <span class="admin-film-duration">Statut par defaut: Hors programmation</span>
                    </div>
                </div>
            </article>
        `;

        if (grid) {
            grid.insertAdjacentHTML('afterbegin', cardHtml);
            return;
        }

        const container = document.createElement('div');
        container.className = 'admin-film-grid';
        container.innerHTML = cardHtml;
        this.catalogBodyTarget.innerHTML = '';
        this.catalogBodyTarget.appendChild(container);
    }

    async toggleStatus(event) {
        const checkbox = event.currentTarget;
        const filmId = checkbox.dataset.filmId;
        if (!filmId) {
            return;
        }

        const desiredStatus = checkbox.checked ? 'disponible' : 'archive';
        const url = this.statusUrlTemplateValue.replace('__ID__', filmId);

        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: desiredStatus })
            });

            if (!response.ok) {
                checkbox.checked = !checkbox.checked;
                return;
            }

            const payload = await response.json();
            const card = checkbox.closest('.admin-film-card');
            const statusEl = card ? card.querySelector('.admin-film-status') : null;
            const toggleLabel = card ? card.querySelector('.admin-toggle-label') : null;

            if (statusEl) {
                statusEl.textContent = payload.label || 'Hors programmation';
                statusEl.classList.toggle('is-live', payload.status === 'disponible');
                statusEl.classList.toggle('is-archived', payload.status !== 'disponible');
            }

            if (toggleLabel) {
                toggleLabel.textContent = payload.status === 'disponible' ? "A l'affiche" : 'Hors programmation';
            }
        } catch (error) {
            checkbox.checked = !checkbox.checked;
        }
    }
}
