/**
 * Editor.js — blocs CollectiveAccess (IdéesCulture)
 *
 * Trois outils partageant la même ergonomie que le plugin Button :
 *  - on saisit un ID (ca_objects / ca_occurrences / ca_sets)
 *  - on valide avec « Set »
 *  - l'affichage est figé dans un aperçu (preferred_labels récupéré en AJAX
 *    via le contrôleur Articles/Display/CALabel)
 *  - le menu contextuel (icône engrenage) ajoute un bouton « Edit » pour
 *    revenir à la saisie (Move up / Delete / Move down sont fournis par le
 *    cœur d'Editor.js).
 *
 * Données sauvegardées : { id }. Le libellé n'est PAS stocké : il est
 * re-calculé côté serveur à l'affichage (getWithTemplate).
 *
 * Expose les globals : CAObjectTool, CAOccurrenceTool, CASetTool
 */
class CAEntityTool {
  static get toolbox() {
    return { title: 'CA Entity', icon: '<svg width="20" height="20" viewBox="0 0 20 20"></svg>' };
  }

  constructor({ data, api, config }) {
    this.api = api;
    this.config = config || {};
    this.data = { id: (data && data.id) ? String(data.id) : '' };
    this.wrapper = null;
  }

  // Surchargé par les sous-classes : 'objects' | 'occurrences' | 'sets'
  get table() { return 'objects'; }

  get urlRoot() { return this.config.urlRoot || ''; }

  render() {
    this.wrapper = document.createElement('div');
    this.wrapper.classList.add('ca-entity-block');
    if (this.data.id) { this._renderPreview(); } else { this._renderForm(); }
    return this.wrapper;
  }

  _renderForm() {
    this.wrapper.innerHTML = '';
    this.wrapper.classList.remove('ca-entity-block--set');

    const input = document.createElement('input');
    input.type = 'text';
    input.value = this.data.id || '';
    input.placeholder = 'ID ' + this.table + '…';
    input.classList.add('ca-entity-input');

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.textContent = 'Set';
    btn.classList.add('ca-entity-set');

    btn.addEventListener('click', () => {
      const v = input.value.trim();
      if (!v) { input.focus(); return; }
      this.data.id = v;
      this._renderPreview();
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); btn.click(); }
    });

    this.wrapper.appendChild(input);
    this.wrapper.appendChild(btn);
    setTimeout(() => input.focus(), 50);
  }

  _renderPreview() {
    this.wrapper.innerHTML = '';
    this.wrapper.classList.add('ca-entity-block--set');

    const box = document.createElement('div');
    box.classList.add('ca-entity-preview');
    box.innerHTML = '<span class="ca-entity-meta">' + this.table + ' #' + this.data.id + '</span> <span class="ca-entity-label">…</span>';
    this.wrapper.appendChild(box);

    const url = this.urlRoot + '/index.php/Articles/Display/CALabel/table/'
      + encodeURIComponent(this.table) + '/id/' + encodeURIComponent(this.data.id);

    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then((r) => r.json())
      .then((d) => { box.innerHTML = (d && d.html) ? d.html : '<em>(introuvable : ' + this.table + ' #' + this.data.id + ')</em>'; })
      .catch(() => { box.innerHTML = '<em>erreur de chargement</em>'; });
  }

  renderSettings() {
    const wrapper = document.createElement('div');
    const edit = document.createElement('div');
    edit.classList.add(this.api.styles.settingsButton);
    edit.title = 'Edit';
    edit.innerHTML = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M4 13.5L13.5 4l2.5 2.5L6.5 16H4v-2.5z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>';
    edit.addEventListener('click', () => { this._renderForm(); });
    wrapper.appendChild(edit);
    return wrapper;
  }

  save() {
    return { id: this.data.id };
  }

  validate(data) {
    return !!(data && String(data.id || '').trim());
  }
}

class CAObjectTool extends CAEntityTool {
  static get toolbox() {
    return {
      title: 'CA Object',
      icon: '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.5l7.5 4.2v8.6L10 18.5 2.5 14.3V5.7L10 1.5z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><path d="M2.7 5.8L10 10l7.3-4.2M10 10v8.3" fill="none" stroke="currentColor" stroke-width="1.1"/></svg>'
    };
  }
  get table() { return 'objects'; }
}

class CAOccurrenceTool extends CAEntityTool {
  static get toolbox() {
    return {
      title: 'CA Occurrence',
      icon: '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M3 3h6.2l7.3 7.3-6.2 6.2L3 9.2V3z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/><circle cx="6.4" cy="6.4" r="1.3" fill="currentColor"/></svg>'
    };
  }
  get table() { return 'occurrences'; }
}

class CASetTool extends CAEntityTool {
  static get toolbox() {
    return {
      title: 'CA Object set',
      icon: '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2l8 4-8 4-8-4 8-4z" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="M2 10l8 4 8-4M2 14l8 4 8-4" fill="none" stroke="currentColor" stroke-width="1.1" stroke-linejoin="round"/></svg>'
    };
  }
  get table() { return 'sets'; }
}
