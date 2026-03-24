type Listener = () => void;

let pending = 0;
const listeners = new Set<Listener>();

function emit() {
  listeners.forEach((l) => l());
}

export const loadingStore = {
  inc() {
    pending += 1;
    emit();
  },
  dec() {
    pending = Math.max(0, pending - 1);
    emit();
  },
  getPending() {
    return pending;
  },
  subscribe(listener: Listener) {
    listeners.add(listener);
    return () => listeners.delete(listener);
  },
};
