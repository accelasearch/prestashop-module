import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react";

const baseUrl =
  window.accelasearch_controller_url.split("?")[0] ??
  "http://localhost:8199/adminPS/index.php";

const token =
  window.accelasearch_controller_token ?? "2a8d59454418c84aba4aa558762b1632";

export const serviceApi = createApi({
  reducerPath: "serviceApi",
  baseQuery: fetchBaseQuery({ baseUrl }),
  endpoints: (builder) => ({
    getFoo: builder.query({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=foo`,
      }),
    }),
  }),
});
