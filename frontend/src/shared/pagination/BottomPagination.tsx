import { buildPageItems } from "./buildPageItems";

type BottomPaginationProps = {
  page: number;
  totalPages: number;
  onChange: (page: number) => void;
};

export default function BottomPagination({
  page,
  totalPages,
  onChange,
}: BottomPaginationProps) {
  if (totalPages <= 1) return null;

  const items = buildPageItems(page, totalPages);

  return (
    <div className="pagination-bar">
      {items.map((item, i) =>
        item === "..." ? (
          <span key={`dots-${i}`} className="small">
            …
          </span>
        ) : (
          <button
            key={item}
            className={`btn pagination-button ${item === page ? "btn-primary" : ""}`}
            onClick={() => onChange(item)}
            aria-current={item === page ? "page" : undefined}
          >
            {item}
          </button>
        ),
      )}
    </div>
  );
}
