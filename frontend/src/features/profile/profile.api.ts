import { http } from "../../shared/api/http";
import type { ChangeMyPasswordPayload } from "./profile.types";

export const profileApi = {
  async changeMyPassword(
    payload: ChangeMyPasswordPayload,
  ): Promise<{ message: string }> {
    const res = await http.patch("/api/me/password", payload);
    return res.data;
  },
};