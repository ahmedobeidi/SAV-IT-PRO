export function buildPageItems(
  page: number,
  totalPages: number,
): Array<number | "..."> {
  if (totalPages <= 7) {
    return Array.from({ length: totalPages }, (_, index) => index + 1);
  }

  const items: Array<number | "..."> = [1];
  const start = Math.max(2, page - 1);
  const end = Math.min(totalPages - 1, page + 1);

  if (start > 2) items.push("...");
  for (let current = start; current <= end; current += 1) items.push(current);
  if (end < totalPages - 1) items.push("...");

  items.push(totalPages);
  return items;
}
