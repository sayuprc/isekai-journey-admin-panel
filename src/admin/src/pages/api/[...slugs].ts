import { app } from '../../server';

const handle = ({ request }: { request: Request }) => app.handle(request);

export const ALL = handle;
