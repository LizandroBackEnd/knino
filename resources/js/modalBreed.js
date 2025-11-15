export default function initModalBreed() {
  try {
    const btnAddBreed = document.getElementById('btn-add-breed');
    const modalAddBreed = document.getElementById('modal-add-breed');
    if (!btnAddBreed || !modalAddBreed) return null;

    const modalBreedSpecies = modalAddBreed.querySelector('#modal-breed-species');
    const modalBreedName = modalAddBreed.querySelector('#modal-breed-name');
    const modalBreedError = modalAddBreed.querySelector('#modal-breed-error');
    const modalBreedCancel = modalAddBreed.querySelector('#modal-breed-cancel');
    const modalBreedSave = modalAddBreed.querySelector('#modal-breed-save');
    const speciesSelect = document.getElementById('pet-species');
    const breedSelect = document.getElementById('pet-breed');

    const breedEndpoint = modalAddBreed.dataset.breedEndpoint || (window && window.__BREED_ENDPOINT__) || '/api/breed';

    function openAddBreedModal() {
      const species = (speciesSelect && speciesSelect.value) || '';
      if (modalBreedSpecies) modalBreedSpecies.value = species;
      if (modalBreedName) modalBreedName.value = '';
      if (modalBreedError) { modalBreedError.style.display = 'none'; modalBreedError.textContent = ''; }
      modalAddBreed.classList.remove('hidden');
      modalAddBreed.classList.add('flex');
      modalBreedName && modalBreedName.focus();
    }

    function closeAddBreedModal() {
      modalAddBreed.classList.remove('flex');
      modalAddBreed.classList.add('hidden');
    }

    async function reloadBreeds(species, preselectNameOrId) {
      if (!breedSelect) return;
      breedSelect.innerHTML = '<option value="">Cargando...</option>';
      if (!species) {
        breedSelect.innerHTML = '<option value="">Seleccione especie primero</option>';
        return;
      }
      try {
  const res = await fetch(breedEndpoint + '/' + encodeURIComponent(species), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) {
          breedSelect.innerHTML = '<option value="">No se encontraron razas</option>';
          return;
        }
        const payload = await res.json();
        const list = Array.isArray(payload) ? payload : (payload.data || []);
        if (!list.length) {
          breedSelect.innerHTML = '<option value="">No se encontraron razas</option>';
          return;
        }
        breedSelect.innerHTML = '<option value="">Selecciona una raza</option>';
        list.forEach(b => {
          const opt = document.createElement('option');
          opt.value = b.id ?? b.value ?? b.name ?? '';
          opt.textContent = b.name ?? b.label ?? b.value ?? opt.value;
          breedSelect.appendChild(opt);
        });

        if (preselectNameOrId) {
          const byId = breedSelect.querySelector('option[value="' + preselectNameOrId + '"]');
          if (byId) {
            breedSelect.value = preselectNameOrId;
          } else {
            for (const opt of breedSelect.options) {
              if (opt.textContent.trim().toLowerCase() === String(preselectNameOrId).trim().toLowerCase()) {
                breedSelect.value = opt.value;
                break;
              }
            }
          }
        }
      } catch (err) {
        console.error('Error reloading breeds', err);
        breedSelect.innerHTML = '<option value="">Error al cargar razas</option>';
      }
    }

    btnAddBreed.addEventListener('click', function () { openAddBreedModal(); });
    modalBreedCancel && modalBreedCancel.addEventListener('click', function () { closeAddBreedModal(); });

    modalBreedSave && modalBreedSave.addEventListener('click', async function () {
      const name = modalBreedName && modalBreedName.value && modalBreedName.value.trim();
      const species = modalBreedSpecies && modalBreedSpecies.value && modalBreedSpecies.value.trim();
      if (modalBreedError) { modalBreedError.style.display = 'none'; modalBreedError.textContent = ''; }
      if (!species) {
        if (modalBreedError) { modalBreedError.textContent = 'Selecciona una especie primero.'; modalBreedError.style.display = 'block'; }
        return;
      }
      if (!name) {
        if (modalBreedError) { modalBreedError.textContent = 'El nombre de la raza es requerido.'; modalBreedError.style.display = 'block'; }
        return;
      }

      try {
        const tokenInput = document.querySelector('input[name="_token"]');
        const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json' };
        if (tokenInput) headers['X-CSRF-TOKEN'] = tokenInput.value;

        const res = await fetch(breedEndpoint, {
          method: 'POST',
          headers: headers,
          credentials: 'same-origin',
          body: JSON.stringify({ name: name, species: species })
        });

        if (res.status === 201) {
          await reloadBreeds(species, name);
          closeAddBreedModal();
          if (window.showToast) window.showToast('Raza agregada', { type: 'success' });
          return;
        }

        if (res.status === 422) {
          const payload = await res.json();
          const errors = payload && payload.errors ? payload.errors : null;
          if (errors && errors.name) {
            if (modalBreedError) { modalBreedError.textContent = Array.isArray(errors.name) ? errors.name.join(', ') : errors.name; modalBreedError.style.display = 'block'; }
            return;
          }
        }

        if (modalBreedError) { modalBreedError.textContent = 'No se pudo agregar la raza. Intenta de nuevo.'; modalBreedError.style.display = 'block'; }
      } catch (err) {
        console.error('Error adding breed', err);
        if (modalBreedError) { modalBreedError.textContent = 'Error de conexión al servidor.'; modalBreedError.style.display = 'block'; }
      }
    });

    // close on overlay click or Escape
    modalAddBreed.addEventListener('click', function (e) {
      if (e.target === modalAddBreed) closeAddBreedModal();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && modalAddBreed.classList.contains('flex')) closeAddBreedModal();
    });

    return { openAddBreedModal, closeAddBreedModal, reloadBreeds };
  } catch (err) {
    console.error('initModalBreed error', err);
    return null;
  }
}
