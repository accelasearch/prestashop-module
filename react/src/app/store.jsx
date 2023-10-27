import { configureStore } from "@reduxjs/toolkit";
import { setupListeners } from "@reduxjs/toolkit/query";
import fooReducer from "../features/foo/fooSlice";
import { serviceApi } from "../services/service";

export const store = configureStore({
  reducer: {
    foo: fooReducer,
    [serviceApi.reducerPath]: serviceApi.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware().concat(serviceApi.middleware),
});

setupListeners(store.dispatch);