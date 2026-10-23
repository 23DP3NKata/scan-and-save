export function parseLatvianDate(value: string): string | null {
  const match = /^(\d{2})\.(\d{2})\.(\d{4})\.?$/.exec(value.trim());
  if (!match) return null;
  const [, day, month, year] = match;
  const date = new Date(`${year}-${month}-${day}T12:00:00`);
  if (!year || +year < 1 || date.getFullYear() !== +year || date.getMonth() + 1 !== +month! || date.getDate() !== +day!) return null;
  return `${year}-${month}-${day}`;
}

export function formatLatvianDate(value: string): string {
  return value ? value.split('-').reverse().join('.') : '';
}

type Control = HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement;

export function localizeValidity(field: Control): void {
  field.setCustomValidity('');
  const validity = field.validity;
  let message = '';
  if (validity.valueMissing) message = 'Lūdzu, aizpildi šo lauku.';
  else if (validity.typeMismatch) message = field instanceof HTMLInputElement && field.type === 'email' ? 'Ievadi derīgu e-pasta adresi.' : 'Ievadi derīgu vērtību.';
  else if (validity.tooShort) message = `Ievadi vismaz ${(field as HTMLInputElement).minLength} rakstzīmes.`;
  else if (validity.tooLong) message = `Ievadi ne vairāk kā ${(field as HTMLInputElement).maxLength} rakstzīmes.`;
  else if (validity.patternMismatch) message = 'Ievadi vērtību norādītajā formātā.';
  else if (validity.rangeUnderflow) message = `Vērtībai jābūt vismaz ${(field as HTMLInputElement).min}.`;
  else if (validity.rangeOverflow) message = `Vērtība nedrīkst pārsniegt ${(field as HTMLInputElement).max}.`;
  else if (validity.stepMismatch || validity.badInput) message = 'Ievadi derīgu skaitli ar atbilstošu precizitāti.';
  if (field.hasAttribute('data-latvian-date') && field.value) {
    const iso = parseLatvianDate(field.value);
    const min = field.dataset.dateMin;
    const max = field.dataset.dateMax;
    if (!iso) message = 'Ievadi derīgu datumu formātā dd.mm.gggg.';
    else if (min && iso < min) message = `Datums nedrīkst būt pirms ${formatLatvianDate(min)}.`;
    else if (max && iso > max) message = `Datums nedrīkst būt pēc ${formatLatvianDate(max)}.`;
  }
  field.setCustomValidity(message);
}

export function installLatvianValidation(): void {
  const update = (event: Event) => {
    const field = event.target;
    if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) localizeValidity(field);
  };
  document.addEventListener('invalid', update, true);
  document.addEventListener('input', update);
  document.addEventListener('change', update);
}
