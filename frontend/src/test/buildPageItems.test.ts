import { buildPageItems } from "../shared/pagination/buildPageItems";

describe("buildPageItems", () => {
  it("returns all pages when totalPages <= 7", () => {
    expect(buildPageItems(1, 5)).toEqual([1, 2, 3, 4, 5]);
  });

  it("returns compact pagination when totalPages > 7", () => {
    expect(buildPageItems(5, 10)).toEqual([1, "...", 4, 5, 6, "...", 10]);
  });

  it("handles near start", () => {
    expect(buildPageItems(2, 10)).toEqual([1, 2, 3, "...", 10]);
  });

  it("handles near end", () => {
    expect(buildPageItems(9, 10)).toEqual([1, "...", 8, 9, 10]);
  });
});