import { createApi, fetchBaseQuery } from "@reduxjs/toolkit/query/react";

const baseUrl =
  window.accelasearch_controller_url.split("?")[0] ??
  "http://localhost:8199/adminPS/index.php";

const token =
  window.accelasearch_controller_token ?? "2a8d59454418c84aba4aa558762b1632";

export const serviceApi = createApi({
  reducerPath: "serviceApi",
  baseQuery: fetchBaseQuery({ baseUrl }),
  tagTypes: ["SHOPS"],
  endpoints: (builder) => ({
    apikeyVerify: builder.mutation({
      query: (key) => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=apikeyverify`,
        method: "POST",
        body: { key },
      }),
    }),
    getShops: builder.query({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=getshops`,
      }),
      provideTags: ["SHOPS"],
    }),
    getAttributes: builder.query({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=getAttributes`,
      }),
    }),
    getCronjobStatus: builder.query({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=getCronjobStatus`,
      }),
    }),
    getLogs: builder.query({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=getLogs`,
      }),
    }),
    disconnect: builder.mutation({
      query: () => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=disconnect`,
        method: "POST",
      }),
    }),
    setShops: builder.mutation({
      query: (shops) => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=setshops`,
        method: "POST",
        body: { shops },
      }),
    }),
    updateConfig: builder.mutation({
      query: (configs) => ({
        url: `?controller=accelasearchAdmin&token=${token}&ajax=1&action=updateconfig`,
        method: "POST",
        body: { configs },
      }),
    }),
  }),
});

export const {
  useApikeyVerifyMutation,
  useGetShopsQuery,
  useSetShopsMutation,
  useGetAttributesQuery,
  useUpdateConfigMutation,
  useGetCronjobStatusQuery,
  useDisconnectMutation,
  useGetLogsQuery,
} = serviceApi;
